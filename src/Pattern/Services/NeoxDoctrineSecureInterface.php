<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services;
    
    interface NeoxDoctrineSecureInterface
    {
        public function encrypt($value, $type): string;

        public function decrypt($value, $type): string;
    }