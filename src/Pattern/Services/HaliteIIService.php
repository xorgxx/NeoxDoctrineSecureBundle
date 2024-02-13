<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services;
    
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Dsn;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services\NeoxDoctrineSecureInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\TypeEncryptorHelper;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class HaliteIIService extends TypeEncryptorHelper implements NeoxDoctrineSecureInterface
    {

        public function __construct(readonly ParameterBagInterface $parameterBag){
            Parent::__construct($parameterBag);
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
            return Parent::encryptHaliteString($value);
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
            return Parent::decryptHaliteString($value);
        }

    }