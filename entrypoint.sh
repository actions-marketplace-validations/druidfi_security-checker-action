#!/bin/sh -l

updates=$($@)

echo "::set-output name=updates::$updates"
