<?php


class ChangePasswordController extends AuthenticatedController
{
    public function render()
    {
        // FIXME! Handle exceptions.
        if ($this->conf->get('security.open_shaarli')) {
            die('You are not supposed to change a password on an Open Shaarli.');
        }

        if (!empty($this->post['setpassword']) && !empty($this->post['oldpassword']))
        {
            if (!tokenOk($this->post['token'])) {
                die('Wrong token.'); // Go away!
            }

            // Make sure old password is correct.
            $oldhash = sha1(
                $_POST['oldpassword']
                . $this->conf->get('credentials.login')
                . $this->conf->get('credentials.salt')
            );
            if ($oldhash != $this->conf->get('credentials.hash')) {
                echo '<script>alert("The old password is not correct.");document.location=\'?do=changepasswd\';</script>';
                return;
            }
            // Save new password
            // Salt renders rainbow-tables attacks useless.
            $this->conf->set('credentials.salt', sha1(uniqid('', true) .'_'. mt_rand()));
            $this->conf->set('credentials.hash', sha1(
                    $_POST['setpassword']
                    . $this->conf->get('credentials.login')
                    . $this->conf->get('credentials.salt')
                )
            );
            try {
                $this->conf->write(isLoggedIn());
            }
            catch(Exception $e) {
                error_log(
                    'ERROR while writing config file after changing password.' . PHP_EOL .
                    $e->getMessage()
                );

                // TODO: do not handle exceptions/errors in JS.
                echo '<script>alert("'. $e->getMessage() .'");document.location=\'?do=tools\';</script>';
                return;
            }
            echo '<script>alert("Your password has been changed.");document.location=\'?do=tools\';</script>';
            return;
        }
        else // show the change password form.
        {
            $this->tpl->renderPage('changepassword');
        }
    }
}