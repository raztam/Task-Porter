<?php
/**
 * Write message to log file for local_taskporter
 *
 * @param string $message The message to write to the log
 * @return bool True if successful, false otherwise
 */
function local_taskporter_write_log($message) {
        global $CFG;
        $logdir = $CFG->dirroot . '/local/taskporter';
        $logfile = $logdir . '/log.txt';
        $timestamp = date('[Y-m-d H:i:s] ');
        $logmessage = $timestamp . $message . PHP_EOL;

        // Set proper permissions for Mac.
        if (file_exists($logfile)) {
            chmod($logfile, 0644);
        }

        return file_put_contents($logfile, $logmessage, FILE_APPEND) !== false;
}
