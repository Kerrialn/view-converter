<?php

namespace ViewConverter\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class JqueryConverterCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('jquery:convert')
            ->setDescription('Converts jQuery JavaScript to vanilla JS')
            ->addArgument('input', InputArgument::REQUIRED, 'JavaScript file or directory path')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output directory (uses source directory if omitted)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview the conversion without writing files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('input');
        $dryRun = (bool) $input->getOption('dry-run');
        $outputDir = $input->getOption('output');

        if (! file_exists($path)) {
            $io->error("Path not found: $path");
            return Command::FAILURE;
        }

        $files = is_file($path)
            ? [$path]
            : $this->findJsFiles($path);

        if (empty($files)) {
            $io->warning("No .js files found in $path");
            return Command::SUCCESS;
        }

        $transformed = 0;
        $skipped = 0;

        foreach ($files as $filePath) {
            $result = $this->transformFile($filePath, $outputDir, $dryRun, $io);
            $result ? $transformed++ : $skipped++;
        }

        if (! $dryRun) {
            $io->success(sprintf(
                'Done: %d file%s converted, %d unchanged.',
                $transformed,
                $transformed === 1 ? '' : 's',
                $skipped
            ));
        }

        return Command::SUCCESS;
    }

    private function transformFile(string $filePath, ?string $outputDir, bool $dryRun, SymfonyStyle $io): bool
    {
        $original = file_get_contents($filePath);
        $transformed = $this->runTransformer($filePath, $io);

        if ($transformed === null) {
            return false;
        }

        if ($transformed === $original) {
            $io->writeln('<fg=gray>Unchanged: ' . basename($filePath) . '</>');
            return false;
        }

        if ($dryRun) {
            $io->section(basename($filePath));
            $io->writeln($transformed);
            return true;
        }

        $destPath = $this->resolveOutputPath($filePath, $outputDir);
        file_put_contents($destPath, $transformed);
        $io->writeln('<info>Converted:</info> ' . $destPath);

        return true;
    }

    private function runTransformer(string $filePath, SymfonyStyle $io): ?string
    {
        $nodeDir = dirname(__DIR__, 2) . '/node';
        $script = $nodeDir . '/jquery-transform.mjs';

        $this->ensureNodeModules($nodeDir, $io);

        $command = sprintf('node %s %s 2>&1', escapeshellarg($script), escapeshellarg($filePath));
        $output = [];
        $code = 0;
        exec($command, $output, $code);

        if ($code !== 0) {
            $io->warning('Skipped ' . basename($filePath) . ': ' . implode(' ', $output));
            return null;
        }

        return implode("\n", $output);
    }

    private function ensureNodeModules(string $nodeDir, SymfonyStyle $io): void
    {
        if (is_dir($nodeDir . '/node_modules')) {
            return;
        }

        $io->info('Installing Node.js dependencies...');
        $command = sprintf('npm install --prefix %s 2>&1', escapeshellarg($nodeDir));
        exec($command, $out, $code);

        if ($code !== 0) {
            throw new RuntimeException('npm install failed: ' . implode("\n", $out));
        }
    }

    private function resolveOutputPath(string $filePath, ?string $outputDir): string
    {
        $name = pathinfo($filePath, PATHINFO_FILENAME);
        $dir = $outputDir !== null
            ? rtrim($outputDir, '/')
            : dirname($filePath);

        return $dir . '/' . $name . '.vanilla.js';
    }

    /**
     * @return string[]
     */
    private function findJsFiles(string $directory): array
    {
        $finder = (new Finder())
            ->in($directory)
            ->name('*.js')
            ->notName('*.vanilla.js')
            ->notPath('node_modules')
            ->files();

        return array_map(fn ($f) => $f->getPathname(), iterator_to_array($finder, false));
    }
}
