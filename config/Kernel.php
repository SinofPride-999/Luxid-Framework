<?php

namespace Luxid\Framework;

// Note: No import Composer classes here since they might not be available at runtime

class Kernel
{
    // Your existing Kernel methods...

    // Static method for Composer hooks
    public static function postCreateProject($event = null)
    {
        $projectRoot = getcwd();
        $vendorDir = $projectRoot . '/vendor';

        // Try to get vendor dir from Composer event if available
        if ($event !== null && method_exists($event, 'getComposer')) {
            try {
                $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
                $projectRoot = dirname($vendorDir);
            } catch (\Throwable $e) {
                // Fallback to current directory
            }
        }

        self::setupJuiceCli($vendorDir, $projectRoot);

        echo PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo "🚀 Luxid Framework installed successfully!" . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo PHP_EOL;
        echo "🍋 To get started:" . PHP_EOL;
        echo "   1. Configure your .env file" . PHP_EOL;
        echo "   2. Run: php juice serve" . PHP_EOL;
        echo PHP_EOL;
        echo "📚 Documentation: https://luxid.dev/docs" . PHP_EOL;
        echo "🐛 Report issues: https://github.com/luxid/framework/issues" . PHP_EOL;
        echo PHP_EOL;
    }

    private static function setupProject(): void
    {
        $projectRoot = getcwd();
        $vendorDir = $projectRoot . '/vendor';

        self::setupJuiceCli($vendorDir, $projectRoot);

        echo PHP_EOL;
        echo "✓ Luxid Framework setup complete!" . PHP_EOL;
        echo PHP_EOL;
    }

    private static function setupJuiceCli(string $vendorDir, string $projectRoot): void
    {
        $juiceSource = $vendorDir . '/luxid/engine/Engine/juice';
        $juiceTarget = $projectRoot . '/juice';

        // Check if juice file already exists and warn
        if (file_exists($juiceTarget)) {
            echo "ℹ️  'juice' CLI already exists in project root" . PHP_EOL;
            echo "   Skipping creation..." . PHP_EOL;
            return;
        }

        // Check if juice file exists in engine
        if (!file_exists($juiceSource)) {
            echo "⚠️  Juice CLI not found in engine package" . PHP_EOL;
            echo "   Expected at: " . $juiceSource . PHP_EOL;
            return;
        }

        // Copy juice file to project root
        if (copy($juiceSource, $juiceTarget)) {
            echo "✓ Created 'juice' CLI tool in project root" . PHP_EOL;
        } else {
            echo "⚠️  Could not copy juice to project root" . PHP_EOL;
            return;
        }

        // Handle platform-specific setup
        if (self::isUnixLike()) {
            // Unix/Linux/macOS - make executable
            chmod($juiceTarget, 0755);
            echo "✓ Made 'juice' executable (Unix/Linux/macOS)" . PHP_EOL;

            // Also ensure vendor/bin/juice exists
            $juiceVendorBin = $vendorDir . '/bin/juice';
            if (!file_exists($juiceVendorBin)) {
                if (!is_dir(dirname($juiceVendorBin))) {
                    mkdir(dirname($juiceVendorBin), 0755, true);
                }
                copy($juiceSource, $juiceVendorBin);
                chmod($juiceVendorBin, 0755);
            }
        } else {
            // Windows - create batch file
            self::createWindowsBatchFile($projectRoot);
        }
    }

    private static function isUnixLike(): bool
    {
        return DIRECTORY_SEPARATOR === '/';
    }

    private static function createWindowsBatchFile(string $projectRoot): void
    {
        $batFile = $projectRoot . '/juice.bat';
        $batContent = '@echo off' . PHP_EOL;
        $batContent .= 'REM Luxid CLI Tool - Windows Batch Wrapper' . PHP_EOL;
        $batContent .= 'echo Luxid CLI Tool' . PHP_EOL;
        $batContent .= 'php "%~dp0juice" %*' . PHP_EOL;

        if (file_put_contents($batFile, $batContent)) {
            echo "✓ Created 'juice.bat' for Windows compatibility" . PHP_EOL;
            echo "  Windows users can run: juice.bat [command]" . PHP_EOL;
        }

        // Also create a PowerShell script for modern Windows
        $ps1File = $projectRoot . '/juice.ps1';
        $ps1Content = '#!/usr/bin/env pwsh' . PHP_EOL;
        $ps1Content .= 'Write-Host "Luxid CLI Tool" -ForegroundColor Cyan' . PHP_EOL;
        $ps1Content .= 'php "$PSScriptRoot/juice" $args' . PHP_EOL;

        if (file_put_contents($ps1File, $ps1Content)) {
            echo "✓ Created 'juice.ps1' for PowerShell users" . PHP_EOL;
        }
    }

    // Optional: Method to run juice setup from web interface if needed
    public static function setupJuiceFromWeb(): void
    {
        $projectRoot = dirname(__DIR__);
        $vendorDir = $projectRoot . '/vendor';

        self::setupJuiceCli($vendorDir, $projectRoot);
    }
}
