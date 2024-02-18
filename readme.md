## THIS PROJECT WILL BE REMOVED DO USE IN PROD !!! 
We will refactor all code in same thing more optimize; an idea is to be more clear
##
## new project Name | **DoctrineEncryptorBundle**  | ==================================


##
# NeoxDoctrineSecureBundle { Symfony 6/7 } 
This bundle provides Encrypt/Decrypt data sensible in a Db system in your application.
Its main goal is to make it simple for you to manage encrypt & decrypt sensible data into Db!
This bundle is to refresh the old bundle [DoctrineEncryptBundle](https://github.com/absolute-quantum/DoctrineEncryptBundle)

The aim of this bundle is to establish, in an automatic and transparent manner, a robust encryption and decryption
system by externalizing data, in strict compliance with European recommendations and the directives of the General Data
Protection Regulation (GDPR).

[![2024-02-13-10-33-55.png](https://i.postimg.cc/66C5K8PK/2024-02-13-10-33-55.png)](https://postimg.cc/145Zc3P7)
## Installation BETA VERSION !!

Install the bundle for Composer !! as is still on a beta version !!

````
  composer require xorgxx/neox-doctrine-secure-bundle
  or 
  composer require xorgxx/neox-doctrine-secure-bundle:0.* or dev-master
````
🚨 You will heva to add in your project. 🚨
````
  composer require paragonie/halite
````
.env file
````
  ....
  # standalone = "buit-in" only type string 255, text 
  # external   = this mode will externalize data in one entity (TODO : redis )
  NEOX_ENCRY_DSN=standalone://redis
  NEOX_ENCRY_SALT="**@#$#*#&%&@$&^@"    # 16 bit
  NEOX_ENCRY_PWS="03~é][a6{1;a7a^e2d"   # password your want (more long, more secure, more time to process)
  ....
````
neox_doctrine_secure.yaml file
````
  neox_doctrine_secure:
      # (default)false or true | it will turn off the bundle. by aware that it will render nothing !! field on front will by empty!!
      # this is only for testing purpose in Development mode !!!
      neox_off: false
      ####
    neox_dsn: "%env(NEOX_ENCRY_DSN)%"
    neox_pws: "%env(NEOX_ENCRY_PWS)%"
    neox_salt: "%env(NEOX_ENCRY_SALT)%"
    neox_encryptor: haliteII # halite or haliteII
  
````
🚨 You will have to make migration to add NeoxEncryptor entity 🚨
````
  symfony make:migration
  symfony doctrine:migrations:migrate
````
**We have only implemented Halite service to Crypt / unCrypt**

**NOTE:** _You may need to use [ symfony composer dump-autoload ] to reload autoloading_

 ..... Done 🎈

## Usage !
In entity, you want to secure field (data) 
````php

  use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor; 
  ....
  
  #[ORM\Column(type: Types::TEXT, nullable: true)]
  #[neoxEncryptor]
  private ?string $content = null;
  
  ....
````
## Important !
Consider the size / length of field you want to crypt !! ex: length:10 
````php

  #[neoxEncryptor]
  #[ORM\Column(length: 20)]
  private ?string $name = null;
  
  "john doe" <- decrypt / encrypt -> "MUIFAOpLp21iX1Dy2ZNkYbby6zo7ADYgVs-hGkNaWR2OF5AbQUMcBKZHigtFVxZiIFWyOTV8Ts-9q_pNAHBxCKcAPZNJjfPgVQglMLAKi0bZicmPlCQKJpRpX2k5IAjAqawOlFsPpD9KikIEFRhuy"
  
````
## Tools power
Occasionally, we may require access to a full range of data (4000 lines or more) for various checks or analyses. However, waiting for hours due to the conversion of encrypted data is not desirable. In such cases, disabling the EventListener is imperative.
````
  use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineTools;
  ...
  // this will stop eventlistener to decrypt
  $neoxTableBuilder->EventListener();
  
  $entity = $parametersRepository->findAll()
  
  // this will restart eventlistener to decrypt
  $neoxTableBuilder->EventListener(true);
  
  // data on the field encrypted will be empty
````


[🚨🚨 **FEATURE ADVANCE** in the box in a future version](Doc/External.md)

## Contributing
If you want to contribute \(thank you!\) to this bundle, here are some guidelines:

* Please respect the [Symfony guidelines](http://symfony.com/doc/current/contributing/code/standards.html)
* Test everything! Please add tests cases to the tests/ directory when:
    * You fix a bug that wasn't covered before
    * You add a new feature
    * You see code that works but isn't covered by any tests \(there is a special place in heaven for you\)
## Todo
~~* Add a Remote system for storage Hash => Key~~
~~* to be able to encrypt/decrypt, according to a propriety type | int, string, phone ....~~
* Custom provider class Encrypt/decrypt.
* Dispatcher to custom code.
* Command wasaaaa : to manage more easily status, encrypt, decrypt ....

## Thanks