# External | Advance read before you do anything 🚨🚨🚨

## Process
Dans ce mode, toutes les données qui possèdent exclusivement l'attribut #[neoxEncryptor] dans l'entité source seront
déplacées vers une autre entité pour etre cryptées. Ce processus garantit que les données ne seront plus visibles dans l'entité source.

## Le lien
Le lien entre l'entité source et l'entité NeoxEncryptor est établi par un algorithme standard, mais vous avez également la possibilité de le définir vous-même, en fonction de vos besoins et de vos préférences.

## TRES IMPORTANT A COMPRENDRE 🚨
Il est crucial de noter qu'il sera relativement simple de passer du mode standalone (décryptage) au mode external, mais que l'inverse n'est pas actuellement possible. Cette limitation découle principalement de la complexité associée au processus de décryptage.

[![Untitled-Diagram-drawio-3.png](https://i.postimg.cc/7Ljcj2vR/Untitled-Diagram-drawio-3.png)](https://postimg.cc/B8cMKtn5)
**CUSTOMIZE** Algorithm record [ key => data ] :
We want to enhance security by complicating the algorithm that automatically associates the source entity with the
encrypted entity in my Symfony application. Currently, this logic is exposed in the source code, which could pose a
security risk if the code is accessible to third parties. To make this association less obvious to a hacker, I would
like to make the algorithm more complex while also providing an easy customization option for users. Thus, even if the
source code is accessible, it would be challenging to easily determine which elements belong to which entities.

# How to customize lien ?
Vous devez créer un subscriber d'événements ex:

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


