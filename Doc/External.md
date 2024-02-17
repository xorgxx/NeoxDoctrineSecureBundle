# External | Advance read before you do anything 🚨🚨🚨

## Process
In this mode, all data that exclusively has the #[neoxEncryptor] attribute in the source entity will be
moved to another entity to be encrypted. This process ensures that the data will no longer be visible in the source entity.

## Link
The link between the source entity and the NeoxEncryptor entity is established by a standard algorithm, but you also have the possibility to define it yourself, according to your needs and preferences.

## VERY IMPORTANT TO UNDERSTAND 🚨
It's important to note that robust data encryption incurs additional costs in terms of computational processing. On average, each encryption operation may require around 0.10 milliseconds of processing per data line. Furthermore, when converting data from one format to another, it's necessary to account for additional processing time. For instance, processing 500 lines may take approximately 3 minutes.

Maintaining consistency in data format across all entities is also crucial to simplify operations. This ensures uniformity in the encryption process and reduces overall system complexity.

During the conversion from one format to another, several steps are required:

    * Decrypt the data in the current format.
    * Modify the schema in the .env file by specifying the new format, for example, NEOX_ENCRY_DSN=external://redis where 'external' can be replaced with 'standalone'.
    * Encrypt the data in the new format.

Similarly, attempting to hydrate a table in the front end with an entity containing encrypted fields will take a significant amount of time!

It is crucial to note that it will be relatively simple to switch from standalone mode (decryption) to external mode, but that the reverse is not currently possible. This limitation mainly arises from the complexity associated with the decryption process.

[![Untitled-Diagram-drawio-3.png](https://i.postimg.cc/7Ljcj2vR/Untitled-Diagram-drawio-3.png)](https://postimg.cc/B8cMKtn5)

**CUSTOMIZE** Algorithm record [ key => data ] :
We want to enhance security by complicating the algorithm that automatically associates the source entity with the
encrypted entity in my Symfony application. Currently, this logic is exposed in the source code, which could pose a
security risk if the code is accessible to third parties. To make this association less obvious to a hacker, I would
like to make the algorithm more complex while also providing an easy customization option for users. Thus, even if the
source code is accessible, it would be challenging to easily determine which elements belong to which entities.

# How to customize Link id ?
You need to create an event subscriber ex:

````
<?php

    /**
     * Created by PhpStorm.
     * Member: xorgxx
     */

    namespace App\EventSubscriber;


    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events\NeoxEncryptorEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use ReflectionClass;
    
    class NeoxEncryptorListener implements EventSubscriberInterface
    {
        
        public function __construct()
        {
        }

        /**
         * getSubscribedEvents.
         *
         * @return array
         */
        public static function getSubscribedEvents(): array
        {
            return array(
                NeoxEncryptorEvent::EVENT_ENCRYPTOR_KEY    => 'onEncryptorKey',
            );
        }
        
        public function onEncryptorKey(NeoxEncryptorEvent $event): void
        {
            /** @var ReflectionClass $p */
            $p  = $event->getEncryptorClass();
            $e  = $event->getEntity()
            
            ....
            
            /**
            * Then, from here, you can do whatever you want!
            *
            * These two variables must also be filled:
            *    ->setMsg() with the key "link"
            *    ->setKey() with the password
            *
            * To be valid, setMsg() must have at least a unique ID from the source entity, for example: getId(), this way it will refer only to one entity!
            *
            * The best way is:
            *    setMSG($p->getName() . "::" . $e->getId()); You can add anything to replace "::"
            *    setKey($p->getShortName()); You can add whatever you like.
            *
            * This will allow you to set the values appropriately.
            **/
            
            /**
            * These values will be processed in this function later to generate the link between them.
            *       private function getEncryptionKey(string $msg = "", string $key = ""): void
            *       {
            *           $key            = new HiddenString($key);
            *           $encryptionKey  = KeyFactory::deriveEncryptionKey($key, $this->getSalt());
            *           $this->indice   = Util::keyed_hash($msg, $encryptionKey,16);
            *       }
            */
        }

    }
````

NeoxEncryptorEvent.php

````
<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events;
    
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Contracts\EventDispatcher\Event;
    use ReflectionClass;
    
    class NeoxEncryptorEvent extends Event
    {
        public CONST EVENT_ENCRYPTOR_KEY = "neox.encryptor.key";
   
        private ?string $msg = null;
        private ?string $key = null;
        
        public function __construct( readonly mixed $EncryptorClass, readonly mixed $entity)
        {
      
        }
        
        public function getEncryptorClass(): mixed
        {
            return $this->EncryptorClass;
        }
        
        public function getMsg(): ?string
        {
            return $this->msg;
        }
        
        public function setMsg(string $msg): void
        {
            $this->msg = $msg;
        }
        
        public function getKey(): ?string
        {
            return $this->key;
        }
        
        public function setKey(string $key): void
        {
            $this->key = $key;
        }
    }
````


