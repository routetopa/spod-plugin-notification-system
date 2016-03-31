#!/usr/bin/env bash
kill $(cat server_pid.txt)
rm server_pid.txt