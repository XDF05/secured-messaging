@extends('conversations.template')
@section('title', 'Messages')
@section('content')
    <script type="text/javascript" src="{{ asset('js/security.js') }}"></script>
    <script>
        // retrieve the public key used for encryption from the user we're talking to
        let public_key_encryption = {!! $public_key_encryption !!};
        public_key_encryption = JSON.parse(public_key_encryption[0].public_key_encryption);
        public_key_encryption = JSON.parse(public_key_encryption);

        // retrieve the public key used for signing from the user we're talking to
        let public_key_signature = {!! $public_key_signature !!};
        public_key_signature = JSON.parse(public_key_signature[0].public_key_signature);
        public_key_signature = JSON.parse(public_key_signature);

        // retrieve our public key used for signing in order to decode our own messages
        let my_public_key_signature = JSON.parse(localStorage.getItem("public_key_signature"));
    </script>
    <div class="container">
        <div class="row">
            @include('conversations.users', ['users' => $users])
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">{{ $user->name }}</div>
                    <div id="test" class="card-body conversations">
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($messages as $message)
                            <div class="row">
                                <div class="col-md-10 {{ $message->id != $user->id ? 'offset-md-2 text-right' : '' }}">
                                    <p>
                                        <strong>{{ $message->id != $user->id ? 'Me' : $message->name }}</strong> <br>
                                    <p id="decryptedMsg{{ $i }}">{!! nl2br(e($message->content)) !!}</p>
                                    <script>
                                        /**
                                         * Function used for message decryption. 
                                         */
                                        (async function() {
                                            // verifie whether the sender is the current user
                                            let currentUserIsSender = {{ "$message->id != $user->id" }};
                                            // retrieves the encrypted message and coverts it back to an ArrayBuffer
                                            let encryptedMsg = convertFromB64("{{ $message->content }}");
                                            // retrieves the initialisation vector used to encrypt the message and coverts it back to an ArrayBuffer
                                            let iv = convertFromB64('{{ $message->iv }}');

                                            let encryptedData = {
                                                "data": encryptedMsg,
                                                "iv": iv
                                            };
                                            // retrieve the digital signature of a message and convert it back to an ArrayBuffer
                                            let digital_signature = convertFromB64("{{ $message->digital_signature }}");
                                            // decrypts the message with public key of the user we're talking to
                                            let msg = await decrypt(public_key_encryption, encryptedData);
                                            // if we're the sender of the message, verify it using our public key
                                            // otherwise, use the public key of the person we're talking to
                                            let verified;
                                            if (currentUserIsSender) {
                                                verified = await verifyMessage(msg, digital_signature, my_public_key_signature);
                                            } else {
                                                verified = await verifyMessage(msg, digital_signature, public_key_signature);
                                            }
                                            // display the decrypted message
                                            if (verified) {
                                                $("#decryptedMsg" + {{ $i }}).text(msg);
                                            } else {
                                                $("#decryptedMsg" + {{ $i }}).text("Digital Signature Verification Failed!");
                                            }

                                        })
                                        ();
                                    </script>
                                    </p>
                                </div>
                            </div>
                            @php
                                $i++;
                            @endphp
                        @endforeach
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form id="messageForm" action="" method="post">
                            @csrf
                            <div class="form-group">
                                <textarea id="messageContent" name="content" placeholder="Write a message" class="form-control"></textarea>
                                <input id="inputIV" type="hidden" name="iv">
                                <input id="inputDigitalSignature" type="hidden" name="digital_signature">
                            </div>
                            <button id="messageSubmit" type="submit" class="btn btn-primary">Send</button>
                        </form>

                        <script>
                            /**
                             * Function used for message encryption. 
                             */
                            $("#messageSubmit").click(async function(event) {
                                // retrieves the message the user sent 
                                let input = $("#messageContent").val();
                                //remove default submit from the button
                                event.preventDefault();
                                // verify whether the message is empty
                                if (input) {
                                    // encrypts the message which the user whiches to send using the public key of the person we're talking to
                                    let encryptedText = await encrypt(public_key_encryption, input);

                                    // sign the message and retrieve the digital signature
                                    let digital_signature = await signMessage(input);

                                    // convert the digital signature, encryptedMessage and initialisation vector to base64 in order to store them inside the DB
                                    let b64Digital_signature = convertToB64(digital_signature);
                                    let b64EncryptedData = convertToB64(encryptedText.data);
                                    let b64iv = convertToB64(encryptedText.iv);

                                    // set the value of the input fields to the base64 values in order to submit them to the server
                                    $("#messageContent").val(b64EncryptedData);
                                    $("#inputIV").val(b64iv);
                                    $("#inputDigitalSignature").val(b64Digital_signature);
                                }
                                $("#messageForm").submit();
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
