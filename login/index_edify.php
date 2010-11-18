<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<html>
    <head>
        <title>Edify Login Page</title>
        
    </head> 
    <body style="font-size:12px;">
        <h2>Returning to this web site?</h2>
          <div class="subcontent loginsub">
            <div class="desc">
              Login here using your username and password<br>(Cookies must be enabled in your browser)<span class="helplink"><a href="http://localhost/moodle_edify/help.php?component=moodle&amp;identifier=cookiesenabled&amp;lang=en" title="Help with Cookies must be enabled in your browser" id="helpicon4ce370b330e3d"><img src="http://localhost/moodle_edify/theme/image.php?theme=standard&amp;image=help&amp;rev=161" alt="Cookies must be enabled in your browser" class="iconhelp"></a></span>        </div>
                    <form action="http://localhost/moodle_edify/login/index.php" method="post" id="login">
              <div class="loginform">

                <div class="form-label"><label for="username">Username</label></div>
                <div class="form-input">
                  <input name="username" id="username" size="15" value="admin" type="text">
                </div>
                <div class="clearer"><!-- --></div>
                <div class="form-label"><label for="password">Password</label></div>
                <div class="form-input">
                  <input name="password" id="password" size="15" value="" type="password">

                  <input id="loginbtn" value="Login" type="submit">
                  <div class="forgetpass"><a href="forgot_password.php">Forgotten your username or password?</a></div>
                </div>
                <div class="clearer"><!-- --></div>
              </div>
            </form>
          </div> 
        
    </body>
</html>


