<?php

namespace Spatie\PersonalDataExport;

interface ExportsPersonalData
{
    public function selectPersonalData(PersonalDataSelection $personalDataSelection): void;

    public function personalDataExportName(): string;

    public function getKey();
}
