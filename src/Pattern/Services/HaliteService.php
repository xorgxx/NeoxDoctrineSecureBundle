<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services;
    
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Dsn;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineSecureInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\TypeEncryptorHelper;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    
    class HaliteService extends TypeEncryptorHelper implements NeoxDoctrineSecureInterface
    {

        public function __construct(readonly Dsn $dsn){
            Parent::__construct($dsn);
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws InvalidSalt
         * @throws \SodiumException
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function encrypt($value, $type): string
        {
            switch ($type) {
                case 'int':
                    break;
                case 'misc':
                    return "misc";
                    break;
                default:
//                    throw new InvalidType();
                    return Parent::encryptHaliteString($value);
            }
            return $value;
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidDigestLength
         * @throws \SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         * @throws InvalidSignature
         */
        public function decrypt($value, $type): string
        {
            switch ($type) {
                case 'phone':
                    break;
                case 'misc':
                    return "misc";
                    break;
                default:
//                    throw new InvalidType();
                    return Parent::decryptHaliteString($value);
            }

             return $value;
        }
        
    }