<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute;
    
    use Attribute;
    use Doctrine\ORM\Mapping\MappingAttribute;
    
    /**
     * The `neoxEncryptor` class is a PHP attribute that can be applied to properties and is used as a
     * placeholder for encryption functionality.
     */
    #[Attribute(Attribute::TARGET_PROPERTY )]
    class neoxEncryptor implements MappingAttribute
    {
        // Placeholder
    }