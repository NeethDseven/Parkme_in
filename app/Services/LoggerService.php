<?php
class LoggerService {
    private $logFile;

    public function __construct() {
        $this->logFile = ROOT_PATH . '/logs/app.log';
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
    }

    public function log($level, $message, $context = []) {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date] [$level]: $message";
        if (!empty($context)) {
            $logMessage .= ' ' . json_encode($context);
        }
        error_log($logMessage . PHP_EOL, 3, $this->logFile);
    }

    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }

    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }

    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
}
