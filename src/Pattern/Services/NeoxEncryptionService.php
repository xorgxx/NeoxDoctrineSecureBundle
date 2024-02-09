<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services;
    
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineSecureInterface;
    
    class NeoxEncryptionService implements NeoxDoctrineSecureInterface
    {
        
        public function encrypt($value, $type): string
        {
            return "ok default";
        }
        
        public function decrypt($value, $type): string
        {
            return "ok default";
        }
    }