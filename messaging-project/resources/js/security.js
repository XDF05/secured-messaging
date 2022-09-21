/**
 * Generates a pair of keys for encryption/decryption and another one signature/verify 
 */
window.generateKeyPairs = async function () {
    await genKeyPairEncryption();
    await genKeyPairSignature();
};

/**
 * Generate the encryption/decryption key pair and exports them to jwk (JSON WebKey) format in order to store them inside the localStorage / DB
 * 
 * ECDH has been used in order to achieve E2EE (end to end encryption).
 * Uses the public key of the destination and private key from the source in order to generate a shared symmetric key (obtained by derivation).
 */
async function genKeyPairEncryption() {
    const keyPair = await window.crypto.subtle.generateKey({
            name: "ECDH",
            namedCurve: "P-256",
        },
        true,
        ["deriveKey"]
    );
    window.crypto.subtle.exportKey("jwk", keyPair.publicKey)
        .then(e => localStorage.setItem("public_key_encryption", JSON.stringify(e)));
    window.crypto.subtle.exportKey("jwk", keyPair.privateKey)
        .then(e => localStorage.setItem("private_key_encryption", JSON.stringify(e)));
};
/**
 * Generate the sign/verify key pair and exports them to jwk (JSON WebKey) format in order to store them inside the localStorage / DB
 * 
 * ECDSA has been used because it's faster than RSA
 */
async function genKeyPairSignature() {
    const keyPair = await window.crypto.subtle.generateKey({
            name: "ECDSA",
            namedCurve: "P-384"
        },
        true,
        ["sign", "verify"]);
    window.crypto.subtle.exportKey("jwk", keyPair.publicKey)
        .then(e => localStorage.setItem("public_key_signature", JSON.stringify(e)));
    window.crypto.subtle.exportKey("jwk", keyPair.privateKey)
        .then(e => localStorage.setItem("private_key_signature", JSON.stringify(e)));
};

/**
 * message encryption facade
 * 
 * @param {JsonWebKey} publicKey Public key from the destination
 * @param {String} message message to be encrypted
 * @returns encrypted message 
 */
window.encrypt = async function (publicKey, message) {
    let privateKey = JSON.parse(localStorage.getItem("private_key_encryption"));

    let derived = await deriveKey(publicKey, privateKey);

    let encrypted = await encryptMessage(message, derived)
    return encrypted;
};
/**
 * message decryption facade
 * 
 * @param {JsonWebKey} publicKey public from the destination
 * @param {*} encryptedMessage encrypted message
 * @returns decrypted message
 */
window.decrypt = async function (publicKey, encryptedMessage) {
    let privateKey = JSON.parse(localStorage.getItem("private_key_encryption"));

    let derived = await deriveKey(publicKey, privateKey);
    let decryptedMessage = await decryptMessage(encryptedMessage, derived)
    return decryptedMessage;
};

/**
 * Derives the public key of the destination and the source's private key in order to generate a symmetric shared key 
 * 
 * AES-GCM has been used because it's relatively fast and it allows ensures data integrity and confidentiality 
 * 
 * @param {JsonWebKey} publicKeyJwk destination public key
 * @param {JsonWebKey} privateKeyJwk source private key
 * @returns shared symmetric key 
 */
async function deriveKey(publicKeyJwk, privateKeyJwk) {
    const publicKey = await window.crypto.subtle.importKey(
        "jwk",
        publicKeyJwk, {
            name: "ECDH",
            namedCurve: "P-256",
        },
        true,
        []
    );
    const privateKey = await window.crypto.subtle.importKey(
        "jwk",
        privateKeyJwk, {
            name: "ECDH",
            namedCurve: "P-256",
        },
        true,
        ["deriveKey"]
    );
    return await window.crypto.subtle.deriveKey({
            name: "ECDH",
            public: publicKey
        },
        privateKey, {
            name: "AES-GCM",
            length: 256
        },
        true,
        ["encrypt", "decrypt"]
    )
};
/**
 * Encrypts a message using the shared symmetric key 
 * 
 * @param {String} text message to be encrypted
 * @param {CryptoKey} derivedKey shared symmetric key
 * @returns JSON containing the encrypted message and the IV used to encrypt it
 */
async function encryptMessage(text, derivedKey) {
    const encodedText = new TextEncoder().encode(text);
    let iv = window.crypto.getRandomValues(new Uint8Array(12));
    console.log(iv);
    const encryptedData = await window.crypto.subtle.encrypt({
            name: "AES-GCM",
            iv: iv
        },
        derivedKey,
        encodedText
    )
    return {
        data: encryptedData,
        iv: iv
    };

};
/**
 * Decrypts a message using the shared symmetric key 
 * 
 * @param {String} messageJSON JSON containing the encrypted message and the IV used to encrypt it
 * @param {CryptoKey} derivedKey shared symmetric key
 * @returns  decrypted message
 */
async function decryptMessage(messageJSON, derivedKey) {
    try {
        let iv = messageJSON.iv;
        let cipheredText = messageJSON.data;
        const algorithm = {
            name: "AES-GCM",
            iv: iv,
        };
        const decryptedData = await window.crypto.subtle.decrypt(
            algorithm,
            derivedKey,
            cipheredText
        )
        return new TextDecoder().decode(decryptedData);
    } catch (e) {
        return `error decrypting message: ${e}`;
    }
};

/**
 * Signs a message in order to ensure non-repudiation & message integrity
 * 
 * @param {String} message message to be signed
 * @returns digital signature of the message
 */
window.signMessage = async function (message) {
    const encodedText = new TextEncoder().encode(message);
    let privateKeyJwk = JSON.parse(localStorage.getItem("private_key_signature"));

    const privateKey = await window.crypto.subtle.importKey(
        "jwk",
        privateKeyJwk, {
            name: "ECDSA",
            namedCurve: "P-384",
        },
        true,
        ["sign"]
    );
    return await window.crypto.subtle.sign({
            name: "ECDSA",
            hash: {
                name: "SHA-384"
            },
        },
        privateKey,
        encodedText
    );
};
/**
 * verifies a message in order to ensure non-repudiation & message integrity
 * 
 * @param {String} message message which has to be verified
 * @param {ArrayBuffer} signature digital signature 
 * @param {JsonWebKey} publicKeyJwk public key from the destination used to sign the message 
 * @returns true or false
 */
window.verifyMessage = async function (message, signature, publicKeyJwk) {
    const encodedText = new TextEncoder().encode(message);
    const publicKey = await window.crypto.subtle.importKey(
        "jwk",
        publicKeyJwk, {
            name: "ECDSA",
            namedCurve: "P-384",
        },
        true,
        ["verify"]
    );
    return await window.crypto.subtle.verify({
            name: "ECDSA",
            hash: {
                name: "SHA-384"
            },
        },
        publicKey,
        signature,
        encodedText
    );
};

/**
 * Converts an ArrayBuffer into base64 in order to store the data easily
 * 
 * @param {ArrayBuffer} data data to convert into
 * @returns 
 */
window.convertToB64 = function (data) {
    return Buffer.from(data).toString('base64');
}
/**
 * Converts a Base64 String back to an ArrayBuffer
 * 
 * @param {String} data base64 string
 * @returns ArrayBuffer containing the data
 */
window.convertFromB64 = function (data) {
    return Buffer.from(data, 'base64');
}
