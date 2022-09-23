#!/bin/env sh

cd /app

# echo '> 运行 PHPStan'
# ./vendor/bin/phpstan analyze -c phpstan.src.neon.dist

echo "> 运行单元测试"
./vendor/bin/phpunit

echo ""
echo "-> 执行 php-cs-fixer"
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
