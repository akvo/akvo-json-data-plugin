#!/bin/sh

for test in TestFeedHandle TestCurlFeedCache; do
  phpunit --bootstrap tests/bootstrap.php tests/$test
done
