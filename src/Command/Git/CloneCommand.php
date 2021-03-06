<?php

declare(strict_types=1);

namespace PhpInvest\Command\Git;

use PhpInvest\Command\Command;
use PhpInvest\Exception\Git\AlreadyExistsException;
use PhpInvest\Invest\Collection\ProjectCollection;
use PhpInvest\Service\Git\GitService;
use PhpInvest\Service\Project\ProjectChoice;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CloneCommand extends Command
{
    public const NAME = 'pi:git:clone';
    public const OPTION_BRANCH = 'branch';

    private GitService $gitService;
    private ProjectChoice $projectChoice;
    private ProjectCollection $projects;

    public function __construct(GitService $gitService, ProjectChoice $projectChoice)
    {
        parent::__construct(self::NAME);
        $this->gitService = $gitService;
        $this->projectChoice = $projectChoice;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Git clone project')
            ->addArgument(ProjectChoice::NAME, InputArgument::REQUIRED, ProjectChoice::DESCRIPTION)
            ->addOption(self::OPTION_BRANCH, 'b', InputOption::VALUE_REQUIRED, 'Branch name', 'master')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Clone project(s)');

        $branch = $input->getOption(self::OPTION_BRANCH);

        foreach ($this->projects as $project) {
            try {
                $this->io->section($project->getName());

                $this->gitService->clone($branch, $project);

                $this->io->success(sprintf('Cloned branch %s from %s', $branch, (string) $project));
            } catch (AlreadyExistsException $e) {
                $this->io->warning($e->getMessage());
            }
        }

        return 1;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->projects = $this->projectChoice->interact($input, $output);
    }
}
