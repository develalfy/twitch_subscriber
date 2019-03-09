@extends('layouts.app')

@section('content')
    <div class="container">

        <!-- Add a placeholder for the Twitch embed -->
        <div id="twitch-embed" class="col-xs-12"></div>

        <!-- Load the Twitch embed script -->
        <script src="https://embed.twitch.tv/embed/v1.js"></script>

        <!-- Create a Twitch.Embed object that will render within the "twitch-embed" root element. -->
        <script type="text/javascript">
            new Twitch.Embed("twitch-embed", {
                width: 854,
                height: 480,
                channel: "{{$streamer['display_name']}}",
                allowfullscreen:true,
            });
        </script>
    </div>
    <br>
    <br>
    <br>
    <h2 class="text-center">Twitch Events</h2>
    <div class="container">
        <div class="row">
            <div style="display:none" class="socket col-xs-12">
                <textarea class="ws-output" rows="20" style="font-family:Courier;width:100%"></textarea>
                <form id="topic-form" class="text-right form-inline" >
                    <label id="topic-label" for="topic-text"></label>
                    <input type="text" id="topic-text" placeholder="Topic">
                    <button type="submit" class="btn">Listen</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section("scripts")
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script>
        var ws;


        function nonce(length) {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            for (var i = 0; i < length; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }
            return text;
        }

        function heartbeat() {
            message = {
                type: 'PING'
            };
            $('.ws-output').append('SENT: ' + JSON.stringify(message) + '\n');
            ws.send(JSON.stringify(message));
        }

        function listen(topic) {
            message = {
                type: 'LISTEN',
                nonce: nonce(15),
                data: {
                    topics: [topic],
                    auth_token: "{{Auth::user()->twitch_token}}"
                }
            };
            $('.ws-output').append('SENT: ' + JSON.stringify(message) + '\n');
            ws.send(JSON.stringify(message));
        }

        function connect() {
            var heartbeatInterval = 1000 * 60; //ms between PING's
            var reconnectInterval = 1000 * 3; //ms to wait before reconnect
            var heartbeatHandle;

            ws = new WebSocket('wss://pubsub-edge.twitch.tv');

            ws.onopen = function (event) {
                $('.ws-output').append('INFO: Socket Opened\n');
                heartbeat();
                listen("channel-bits-events-v1.149747285");
                heartbeatHandle = setInterval(heartbeat, heartbeatInterval);
            };

            ws.onerror = function (error) {
                $('.ws-output').append('ERR:  ' + JSON.stringify(error) + '\n');
            };

            ws.onmessage = function (event) {
                message = JSON.parse(event.data);
                $('.ws-output').append('RECV: ' + JSON.stringify(message) + '\n');
                if (message.type == 'RECONNECT') {
                    $('.ws-output').append('INFO: Reconnecting...\n');
                    setTimeout(connect, reconnectInterval);
                }
            };

            ws.onclose = function () {
                $('.ws-output').append('INFO: Socket Closed\n');
                clearInterval(heartbeatHandle);
                $('.ws-output').append('INFO: Reconnecting...\n');
                setTimeout(connect, reconnectInterval);
            };

        }

        $(function () {
            connect();
            $('.socket').show();
            $('#topic-label').text("Enter a topic to listen to");
        });
        $('#topic-form').submit(function (event) {
            listen($('#topic-text').val());
            event.preventDefault();
        });
    </script>
@endsection
