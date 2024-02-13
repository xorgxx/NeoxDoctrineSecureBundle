<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Dsn;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    use ParagonIE\Halite\Halite;
    use ParagonIE\Halite\Symmetric\Crypto;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\HiddenString\HiddenString;
    use ParagonIE\Halite\Util;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class TypeEncryptorHelper
    {
        // Use random_bytes(16); to generate the salt "\xdd\x7b\x1e\x38\x75\x9f\x72\x86\x0a\xe9\xc8\x58\xf6\x16\x0d\x3b":
        // \x2a\x2a\x40\x23\x24\x23\x2a\x23\x26\x25\x26\x40\x25\x26\x5e\x40 = **@#$#*#&%&@$&^@
        private const SALT = "**@#$#*#&%&@$&^@";
        
        public function __construct(private ParameterBagInterface $parameterBag)
        {
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws \SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function encryptHaliteString($value): string
        {
            $Halite = Halite::VERSION_PREFIX;
            // if it is already Encrypted then Decrypted
            if (!preg_match("/^{$Halite}/", $value)) {
                [$encryptionKey, $message] = $this->getEncryptionKey($value);
                return Crypto::encrypt($message, $encryptionKey);
            }
            return $value;
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws \SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function decryptHaliteString($value): string
        {
            $Halite = Halite::VERSION_PREFIX;
            
            // only for testing purpose
            // $value = "dede";
            // $value = $this->encryptHaliteString($value);
            
            // if it is already Encrypted then Decrypted
            if (preg_match("/^{$Halite}/", $value)) {
                [$encryptionKey, $message] = $this->getEncryptionKey($value);
                return Crypto::decrypt($message->getString(), $encryptionKey)->getString();
            }
            return $value;
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidKey
         * @throws \SodiumException
         * @throws InvalidSalt
         */
        private function getEncryptionKey(string $msg = ""): array
        {
            
            $key        = new HiddenString($this->parameterBag->get("neox_doctrine_secure.neox_pws"));
            $message    = new HiddenString($msg);
            $encryptionKey = KeyFactory::deriveEncryptionKey($key, self::SALT);
            
            return [$encryptionKey, $message];
        }
        
    }