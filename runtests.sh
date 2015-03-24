#!/bin/sh

for test in TestFeedHandle ; do
  phpunit --bootstrap tests/bootstrap.php tests/$test
done
