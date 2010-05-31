<?php
/*
 * Copyright 2010 CosmoCode GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Will be thrown if the execution on the backend failed.
 *
 * @author Tobias Sarnowski
 */
class PalavaExecutionException extends PalavaException {

    public $exception;

    public function __construct($exception) {
        parent::__construct($exception['message']);
        $this->exception = $exception;
    }

    public function getName() {
        return $this->exception['name'];
    }

    public function getStacktrace() {
        return $this->exception['stacktrace'];
    }

    public function getFormattedStacktrace() {
        return $this->formatStacktrace($this->getStacktrace());
    }

    public function toMap() {
        return array(
            'name' => $this->getName(),
            'message' => parent::getMessage(),
            'stacktrace' => $this->getStacktrace()
        );
    }

    private function formatStacktrace($stackTrace) {
        $out = '';
        foreach ($stackTrace as $step) {
            $out .= 'at '.$step['class'].'.'.$step['method'].'('.$step['filename'].':'.$step['line'].")\n";
        }
        return $out;
    }
}

?>