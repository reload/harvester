<?php
namespace reloaddk\HarvesterBundle;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class AdminUtilities
{
    /**
     * Generate a 6 character random string.
     *
     * @return string
     */
    public function generatePassword() {
        $generator = new UriSafeTokenGenerator();
        $token = $generator->generateToken();
        return substr($token, 0, 6);
    }
}