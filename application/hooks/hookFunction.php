<?php
if (!function_exists('postControllerConstructor')) {
    function postControllerConstructor()
    {
        //exception handling
        set_exception_handler('returnFail');
    }
}
