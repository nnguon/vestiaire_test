<?php

namespace VestiaireCollective\Gateway;

use  VestiaireCollective\Gateway\Request\CaptureRequest;
use VestiaireCollective\Gateway\Result\CaptureResult;
interface CaptureGatewayInterface 
{
    public function capture(CaptureRequest $captureRequest): CaptureResult;
}
