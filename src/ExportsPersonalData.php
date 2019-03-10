<?php

namespace Spatie\PersonalDataExport;


interface ExportsPersonalData
{
    public function selectPersonalData(PersonalDataSelection $personalData): void;

    public function getPersonalDataExportName(): string;

    public function getKey();
}