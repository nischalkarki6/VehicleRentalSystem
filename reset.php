<?php
require_once "config/config.php";

setFlash("error", "Password resets now use a one-time code. Please request a new code.");
redirect("forgot_password.php");
