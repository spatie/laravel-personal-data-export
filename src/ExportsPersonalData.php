<?php

namespace Spatie\PersonalDataExport;

interface ExportsPersonalData
{
    public function selectPersonalData(PersonalDataSelection $personalData): void;

    public function personalDataExportName(): string;

    public function getKey();
}