### PHPCS for Legacy codebases

Can't get a monolitic code base up to scratch but would like incremental progress. PHPCS for legacy allows new standards to be implemented incrementally, by only enforcing them on new / edited code via checking the `git diff` output.

## Install

With composer simply `composer require -dev rlweb/phpcs-for-legacy`.

## Usage

By running you initate the binary `vendor/bin/phpcslegacy run`

# How it works

- Firstly gets a output of `git diff head`
- Transforms the patch output into a useful array of `[filePath => [1,2,3,...]` to changed lines. For each changed line, we include the previous and the next line.
- Then it will run the PHPCS command against these files
- And then create a diff of the issues on the lines.

And output these only!
