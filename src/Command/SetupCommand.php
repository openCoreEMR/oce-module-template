<?php

/**
 * Setup command for initializing a new OpenEMR module from template
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    OpenCoreEMR <support@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\YourModuleName\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'module:setup',
    description: 'Initialize a new OpenEMR module from this template'
)]
class SetupCommand extends Command
{
    private const PLACEHOLDER_VENDOR = '{VendorName}';
    private const PLACEHOLDER_VENDOR_LOWER = '{vendorname}';
    private const PLACEHOLDER_PREFIX = '{vendor-prefix}';
    private const PLACEHOLDER_MODULE = '{ModuleName}';
    private const PLACEHOLDER_MODULE_LOWER = '{modulename}';

    private SymfonyStyle $io;

    /** @var array<string, string> */
    private array $replacements = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('OpenEMR Module Setup');
        $this->io->text([
            'This wizard will help you set up a new OpenEMR module from the template.',
            'It will replace placeholder values with your module-specific information.',
        ]);
        $this->io->newLine();

        // Step 1: Determine if internal or external use
        $useType = $this->askUseType();

        // Step 2: Get module name
        $moduleName = $this->askModuleName();

        // Step 3: Get vendor information based on use type
        if ($useType === 'internal') {
            $this->setupInternalReplacements($moduleName);
        } else {
            $this->setupExternalReplacements($moduleName);
        }

        // Step 4: Show summary and confirm
        if (!$this->confirmReplacements()) {
            $this->io->warning('Setup cancelled. No files were modified.');
            return Command::SUCCESS;
        }

        // Step 5: Perform replacements
        $this->performReplacements();

        // Step 6: Ask if user wants to remove setup files
        if ($this->askRemoveSetup()) {
            $this->removeSetupFiles();
        }

        $this->io->success('Module setup completed successfully!');
        $this->io->text([
            'Next steps:',
            '1. Run: composer install',
            '2. Run: composer code-quality',
            '3. Start building your module!',
        ]);

        return Command::SUCCESS;
    }

    private function askUseType(): string
    {
        $question = new ChoiceQuestion(
            'Is this module for internal OpenCoreEMR use or external/community use?',
            ['internal' => 'Internal OpenCoreEMR (use oce- prefix)', 'external' => 'External/Community (use oe- prefix)'],
            'external'
        );

        $helper = $this->getHelper('question');
        return $helper->ask($this->io->createInput(), $this->io, $question);
    }

    private function askModuleName(): string
    {
        $question = new Question('Enter the module name (lowercase-with-hyphens, e.g., "lab-integration"): ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Module name cannot be empty');
            }
            if (!preg_match('/^[a-z][a-z0-9-]*$/', $answer)) {
                throw new \RuntimeException(
                    'Module name must be lowercase with hyphens (e.g., "lab-integration")'
                );
            }
            return $answer;
        });

        $helper = $this->getHelper('question');
        return $helper->ask($this->io->createInput(), $this->io, $question);
    }

    private function setupInternalReplacements(string $moduleName): void
    {
        $moduleNamePascal = $this->toPascalCase($moduleName);

        $this->replacements = [
            self::PLACEHOLDER_VENDOR => 'OpenCoreEMR',
            self::PLACEHOLDER_VENDOR_LOWER => 'opencoreemr',
            self::PLACEHOLDER_PREFIX => 'oce',
            self::PLACEHOLDER_MODULE => $moduleNamePascal,
            self::PLACEHOLDER_MODULE_LOWER => $moduleName,
        ];
    }

    private function setupExternalReplacements(string $moduleName): void
    {
        $moduleNamePascal = $this->toPascalCase($moduleName);

        // Ask for vendor name
        $vendorQuestion = new Question(
            'Enter vendor name (PascalCase, e.g., "MyOrg" or use "OpenEMR" for community modules): ',
            'OpenEMR'
        );
        $vendorQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Vendor name cannot be empty');
            }
            if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $answer)) {
                throw new \RuntimeException('Vendor name must be PascalCase (e.g., "MyOrg")');
            }
            return $answer;
        });

        $helper = $this->getHelper('question');
        $vendorName = $helper->ask($this->io->createInput(), $this->io, $vendorQuestion);

        $this->replacements = [
            self::PLACEHOLDER_VENDOR => $vendorName,
            self::PLACEHOLDER_VENDOR_LOWER => strtolower($vendorName),
            self::PLACEHOLDER_PREFIX => 'oe',
            self::PLACEHOLDER_MODULE => $moduleNamePascal,
            self::PLACEHOLDER_MODULE_LOWER => $moduleName,
        ];
    }

    private function toPascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    private function confirmReplacements(): bool
    {
        $this->io->section('Configuration Summary');
        $this->io->table(
            ['Placeholder', 'Will be replaced with'],
            [
                ['Vendor Name', $this->replacements[self::PLACEHOLDER_VENDOR]],
                ['Vendor (lowercase)', $this->replacements[self::PLACEHOLDER_VENDOR_LOWER]],
                ['Prefix', $this->replacements[self::PLACEHOLDER_PREFIX]],
                ['Module Name (PascalCase)', $this->replacements[self::PLACEHOLDER_MODULE]],
                ['Module Name (lowercase)', $this->replacements[self::PLACEHOLDER_MODULE_LOWER]],
            ]
        );

        $this->io->newLine();
        $this->io->text('Examples of what will be generated:');
        $this->io->listing([
            sprintf('Namespace: %s\\Modules\\%s', $this->replacements[self::PLACEHOLDER_VENDOR], $this->replacements[self::PLACEHOLDER_MODULE]),
            sprintf('Package: %s/%s-module-%s', $this->replacements[self::PLACEHOLDER_VENDOR_LOWER], $this->replacements[self::PLACEHOLDER_PREFIX], $this->replacements[self::PLACEHOLDER_MODULE_LOWER]),
            sprintf('Module ID: %s-module-%s', $this->replacements[self::PLACEHOLDER_PREFIX], $this->replacements[self::PLACEHOLDER_MODULE_LOWER]),
        ]);

        $question = new ConfirmationQuestion('Proceed with these settings? (yes/no) ', false);
        $helper = $this->getHelper('question');
        return $helper->ask($this->io->createInput(), $this->io, $question);
    }

    private function performReplacements(): void
    {
        $this->io->section('Performing replacements');

        $rootDir = dirname(__DIR__, 2);
        $files = $this->getFilesToProcess($rootDir);

        $progressBar = $this->io->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $this->processFile($file);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine(2);
        $this->io->success(sprintf('Processed %d files', count($files)));
    }

    /**
     * Get list of files to process for replacements
     *
     * @return array<string>
     */
    private function getFilesToProcess(string $rootDir): array
    {
        $files = [];

        // Single files to process
        $singleFiles = [
            $rootDir . '/composer.json',
            $rootDir . '/version.php',
            $rootDir . '/README.md',
            $rootDir . '/GETTING_STARTED.md',
            $rootDir . '/CLAUDE.md',
            $rootDir . '/phpcs.xml',
            $rootDir . '/openemr.bootstrap.php',
        ];

        foreach ($singleFiles as $file) {
            if (file_exists($file) && is_file($file)) {
                $files[] = $file;
            }
        }

        // Directories to search recursively
        $searchDirs = [
            $rootDir . '/src',
            $rootDir . '/public',
            $rootDir . '/templates',
        ];

        // Directories to exclude
        $excludeDirs = ['vendor', 'node_modules', '.git'];

        foreach ($searchDirs as $dir) {
            if (is_dir($dir)) {
                $files = array_merge($files, $this->getFilesRecursive($dir, $excludeDirs));
            }
        }

        return $files;
    }

    /**
     * Get files recursively from a directory
     *
     * @param array<string> $excludeDirs
     * @return array<string>
     */
    private function getFilesRecursive(string $dir, array $excludeDirs): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();

                // Check if file is in excluded directory
                $shouldExclude = false;
                foreach ($excludeDirs as $excludeDir) {
                    if (str_contains($filePath, '/' . $excludeDir . '/')) {
                        $shouldExclude = true;
                        break;
                    }
                }

                if (!$shouldExclude) {
                    $files[] = $filePath;
                }
            }
        }

        return $files;
    }

    private function processFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            $this->io->warning(sprintf('Could not read file: %s', $filePath));
            return;
        }

        $newContent = str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $content
        );

        if ($content !== $newContent) {
            file_put_contents($filePath, $newContent);
        }
    }

    private function askRemoveSetup(): bool
    {
        $question = new ConfirmationQuestion(
            'Remove setup files (bin/setup, src/Command/SetupCommand.php)? (yes/no) ',
            true
        );
        $helper = $this->getHelper('question');
        return $helper->ask($this->io->createInput(), $this->io, $question);
    }

    private function removeSetupFiles(): void
    {
        $rootDir = dirname(__DIR__, 2);
        $filesToRemove = [
            $rootDir . '/bin/setup',
            $rootDir . '/src/Command/SetupCommand.php',
        ];

        foreach ($filesToRemove as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->io->text(sprintf('Removed: %s', basename($file)));
            }
        }

        // Remove Command directory if empty
        $commandDir = $rootDir . '/src/Command';
        if (is_dir($commandDir) && count(scandir($commandDir)) === 2) {
            rmdir($commandDir);
            $this->io->text('Removed empty Command directory');
        }
    }
}
