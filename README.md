# Patch Execution Manager

## Installation

Install the package via Composer:

```bash
composer require johan-code/patch-runner
```

Publish the package configuration file (optional):

```bash
php artisan vendor:publish --tag=patch-runner-config
```

Run migrations:

```bash
php artisan migrate
```

## Console Commands

The package provides the following console commands:

- `php artisan make:patch` Generates a new patch file with a "run" method.
- `php artisan patch` Runs all pending patches that haven't been executed yet.
- `php artisan patch:status` Displays the status of patches (executed or not).

## Config File

After publishing the package configuration file, you can modify the behavior of the patch runner by editing the config/patch-runner.php file.

## Usage

Here's an example of how to use the package:

1. Generate a new patch file: `php artisan make:patch MyPatch`

2. Implement the "run" method in the generated patch file ("MyPatch.php").

3. Run pending patches: `php artisan patch`

4. Check the status of patches: `php artisan patch:status`

## License

This package is open-source software licensed under the MIT License. You can find the license text in the [LICENSE](LICENSE) file.



