<?php

namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command\Helper\helperCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'neox:encryptor:wasaaaa',
    description: 'Add a short description for your command',
)]
class NeoxEncryptorStatusCommand extends Command
{
    
    public readonly helperCommand $helperCommand;
    
    public function __construct(helperCommand $helperCommand)
    {
        $this->helperCommand = $helperCommand;
        parent::__construct();
        
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io                         = new SymfonyStyle($input, $output);
        $entitiesWithProperties     = $this->helperCommand->getList()->entitiesWithProperties;
        $entity[]                   = "ALL";
        
        foreach ($entitiesWithProperties as $entityData) {
            $io->title(sprintf('[Find in] Entity : %s ', $entityData['entity']));
            $io->text($entityData["properties"]);
            $entity[] = $entityData['entity'];
        }
        
        if ($entitiesWithProperties) {
            $io->newLine();
            
            $question           = new ChoiceQuestion('Select action : default [Finish]', ["Finish", "Encrypt", "Decrypt"], "Finish");
            $action             = $this->getHelper('question')->ask($input, $output, $question);
            
            switch ($action) {
                case "Finish":
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                case "Encrypt":
                case "Decrypt":
                    break;
                default:
                    return Command::SUCCESS;
            }

            // Ask user which entity should be moved.
            $question           = new ChoiceQuestion("Please choose the ENTITY you want to {$action}:", $entity);
            $question->setErrorMessage('ENTITY : %s does not exist.');
            $processing             = $this->getHelper('question')->ask($input, $output, $question);
            
            // loop through one/all entities to encrypt/decrypt
            if ($processing === "ALL") {
                foreach ($entitiesWithProperties as $entity) {
                    $io->warning("Processing [$action] Entity : {$entity['entity']}");
                }
            }else{
                $io->warning("Processing [$action] Entity : {$entity['entity']}");
            }
            
            
            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        }
        


        return Command::SUCCESS;
    }
}
