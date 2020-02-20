<?php
namespace App\Command;

use App\Entity\Excursion;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOldExcursionsCommand extends Command
{
    protected static $defaultName = 'app:excursions:purge';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Report the daily counters states')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('date', 'd', InputOption::VALUE_OPTIONAL, 'From date (Format: Y-m-d, default one month)'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $date = (new DateTime())->sub(new DateInterval('P1M'));
            if ($input->getOption('date')) {
                $date = new DateTime($input->getOption('date'));
            }
            $removed = $this->em->getRepository(Excursion::class)->purge($date);
            $output->writeln(sprintf('Purge succeed ! %s entit%s removed.', $removed, ($removed>1?'ies':'y')));
        } catch (\Exception $e) {
            $output->writeln('Error during purge: ' . $e->getMessage());
        }

        return 0;
    }
}