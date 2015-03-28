#!/bin/sh

for test in TestFeedHandle TestCurlFeedCache TestDatabaseFeedStore; do
  phpunit --bootstrap tests/bootstrap.php tests/$test
done
