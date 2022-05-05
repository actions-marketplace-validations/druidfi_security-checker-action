#!/bin/sh -l

updates=$($@)

updates="${updates//'%'/'%25'}"
updates="${updates//$'\n'/'%0A'}"
updates="${updates//$'\r'/'%0D'}"

echo "::set-output name=updates::$updates"
