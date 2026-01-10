<?php

namespace Modules\Installer\Traits;

use Exception;
use Illuminate\Support\Facades\File;
use Log;

trait LicenseTrait {

    private function isVerifiedLicense(): bool
    {
        $licensePath = 'app/verified_license.json';
        return File::exists(storage_path($licensePath)) ? true : false;
    }

    private function getVerifiedLicenseData(): array|null
    {
        $licensePath = storage_path('app/verified_license.json');

        if (File::exists($licensePath)) {
            $licenseContent = File::get($licensePath);
            return json_decode($licenseContent, true);
        }

        return null;
    }

    private function deleteVerifiedLicenseFile(): bool
    {
        $licensePath = storage_path('app/verified_license.json');

        try {
            if (File::exists($licensePath)) {
                File::delete($licensePath);
            }
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting verified license file: ' . $e->getMessage());
            return false;
        }
    }

    private function getLicenseData(): array|null
    {
        $licensePath = storage_path('app/license.json');

        if (File::exists($licensePath)) {
            $licenseContent = File::get($licensePath);
            return json_decode($licenseContent, true);
        }

        return null;
    }

    private function generateVerifiedLicenseFile($response = null, $isAdmin = false): bool
    {

        $license = [
            'domain' => request()->getHost(),
            'ip' => request()->ip(),
            'is_admin' => $isAdmin,
        ];

        if(is_null($response) && $isAdmin == false){
            $license['purchase_code'] = null;
            $license['buyer'] = "AQLOVA";
            $license['license'] = "Single Domain";
            $license['expired'] = "Unlimited";
        }else{
            $license['purchase_code'] = $response['purchase_code'] ?? null;
            $license['buyer'] = $response['item']['buyer'] ?? null;
            $license['license'] = $response['item']['license'] ?? null;
            $license['expired'] = $response['item']['expired'] ?? null;
        }


        $licensePath = storage_path('app/verified_license.json');
        $licenseContent = json_encode($license, JSON_PRETTY_PRINT);

        try {

            file_put_contents($licensePath, $licenseContent);

            return true;
        } catch (Exception $e) {
            Log::error('Error generating verified license file: ' . $e->getMessage());
            return false;
        }
    }

    private function generateLicenseDeactiveFile($isAdmin = false): bool
    {

        $data = [
            'deactivated_at' => now()->toDateTimeString(),
            'is_admin' => $isAdmin,
        ];

        $licensePath = storage_path('app/license_deactivate.json');

        $licenseContent = json_encode($data, JSON_PRETTY_PRINT);

        try {

            file_put_contents($licensePath, $licenseContent);

            return true;
        } catch (Exception $e) {
            Log::error('Error generating license deactivate file: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLicenseDeactiveFile(): bool
    {
        $licensePath = storage_path('app/license_deactivate.json');

        try {
            if (File::exists($licensePath)) {
                File::delete($licensePath);
            }

            $this->deleteVerifiedLicenseFile();

            return true;
        } catch (Exception $e) {
            Log::error('Error deleting license deactivate file: ' . $e->getMessage());
            return false;
        }
    }


    private function generateLicenseFile($purchaseCode = null, $isAdmin = false): bool
    {

        $license = [
            'license' => config('app.name'),
            'domain' => request()->getHost(),
            'purchase_code' => $purchaseCode,
            'ip' => request()->ip(),
            'is_admin' => $isAdmin,
        ];

        $licensePath = storage_path('app/license.json');
        $licenseContent = json_encode($license, JSON_PRETTY_PRINT);

        try {

            file_put_contents($licensePath, $licenseContent);

            return true;
        } catch (Exception $e) {
            Log::error('Error generating license file: ' . $e->getMessage());
            return false;
        }
    }


}
