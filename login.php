<?PHP
include ("includes/connect.php");
include ("includes/language.php");
include ("includes/EpiCurl.php");
include ("includes/EpiOAuth.php");
include ("includes/OSMOAuth.php");
$pagetitle = "Login/logout";
include ("includes/header.php");
?>
        <style>
        
        button {
            border-radius:5px;
            background:#54af29;
            border-color:#2C7505;
            color:#fff;
            width:145px;
            font-size:25px;
            text-shadow:1px 1px -1px #2C7505;
            padding:10px;
        }
        button.done {
            background:#2C7505;
        }
        button:hover {
            background: #2C7505;
        }
        #user {
            display:none;
            font:normal 15px/20px 'Helvetica Neue', sans-serif;
        }
        h1, h2 {
            margin: 10px 0;
        }
        </style>
         <button id='authenticate'>login</button>
        <button id='logout'>logout</button>
        <div id='user'>
            <h1 id='display_name'></h1>
            <h2 id='id'></h2>
            Changesets: <span id='count'></span>
        </div>
        <script src='osmauth.js'></script>
        <script>
        var auth = osmAuth({
            oauth_secret: '<?= CLIENT_SECRET ?>',
            oauth_consumer_key: '<?= CLIENT_ID ?>'
        });
        function done(err, res) {
            if (err) {
                document.getElementById('user').innerHTML = 'error! try clearing your browser cache';
                document.getElementById('user').style.display = 'block';
                return;
            }
            var u = res.getElementsByTagName('user')[0];
            var changesets = res.getElementsByTagName('changesets')[0];
            var o = {
                display_name: u.getAttribute('display_name'),
                id: u.getAttribute('id'),
                count: changesets.getAttribute('count')
            };
            
            <?php if (!isset($_SESSION["osm_user"]))
            {
            ?>
            $.ajax({
                    url : 'ajax/ajax-user.php',
                    data : 'display_name='+u.getAttribute('display_name')+'&id='+u.getAttribute('id'),
                    type : 'POST',
                    success : function(data,status) {

                            document.location.href='/preferences.php';

                    },
                    error : function() {
                            null;
                    }
            });
            <?php
            }
            ?>
                                
                                
            for (var k in o) {
                document.getElementById(k).innerHTML = o[k];
            }
            document.getElementById('user').style.display = 'block';
        }

        document.getElementById('authenticate').onclick = function() {
            auth.authenticate(function() {
                update();
            });
        };

        function showDetails() {
            auth.xhr({
                method: 'GET',
                path: '/api/0.6/user/details'
            }, done);
        }

        function hideDetails() {
            document.getElementById('user').style.display = 'none';
        }

        document.getElementById('logout').onclick = function() {
            auth.logout();
            update();
        };

        function update() {
            if (auth.authenticated()) {
                document.getElementById('authenticate').className = 'done';
                document.getElementById('logout').className = '';
                showDetails();
            } else {
                document.getElementById('authenticate').className = '';
                document.getElementById('logout').className = 'done';
                hideDetails();
            }
        }

        update();
        </script>
    </body>
</html>
