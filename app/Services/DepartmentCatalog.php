<?php

namespace App\Services;

class DepartmentCatalog
{
    /**
     * Structured Academic Divisions & Departments
     */
    public const DIVISIONS = [
        'School of Land Administration & Surveying' => [
            'Land Administration and Surveying',
            'Geomatics Engineering',
            'Geographic Information Systems (GIS) and Remote Sensing',
            'Urban Planning and Design',
            'Cadastral Surveying',
        ],
        'College of Business, Economics & Finance' => [
            'Accounting & Finance',
            'Banking and Finance',
            'Business Administration',
            'Cooperative Accounting and Auditing',
            'Development Economics',
            'Economics',
            'Hotel and Tourism Management',
            'Logistics and Supply Chain Management',
            'Management',
            'Marketing Management',
            'Project Management',
            'Public Administration and Development Management (PADM)',
        ],
        'Institute of Technology (Engineering)' => [
            'Architecture',
            'Biomedical Engineering',
            'Chemical Engineering',
            'Civil Engineering',
            'Computer Science',
            'Construction Technology & Management (COTM)',
            'Electrical & Computer Engineering',
            'Electro-Mechanical Engineering',
            'Environmental Engineering',
            'Food Process Engineering',
            'Hydraulic & Water Resources Engineering',
            'Information Technology',
            'Irrigation and Drainage Engineering',
            'Manufacturing & Industrial Engineering',
            'Mechanical Engineering',
            'Mining Engineering',
            'Software Engineering',
        ],
        'College of Agriculture & Veterinary Medicine' => [
            'Agricultural Economics',
            'Animal Science',
            'Horticulture',
            'Natural Resource Management (NRM)',
            'Plant Science',
            'Rural Development and Agricultural Extension (RDAE)',
            'Soil Resource and Watershed Management',
            'Veterinary Medicine (DVM)',
        ],
        'College of Health & Medical Sciences' => [
            'Anesthesia',
            'Dental Medicine',
            'Human Nutrition',
            'Medical Laboratory Sciences (MLS)',
            'Medicine',
            'Midwifery',
            'Nursing',
            'Optometry',
            'Pharmacy',
            'Public Health',
            'Radiology & Medical Imaging Technology',
        ],
        'College of Social Sciences, Humanities & Education' => [
            'Biology',
            'Chemistry',
            'Educational Psychology',
            'English Language & Literature',
            'Ethiopian Language(s) and Literature (Amharic)',
            'Geography and Environmental Studies',
            'History and Heritage Management',
            'Journalism & Communication',
            'Law',
            'Mathematics',
            'Physics',
            'Political Science and International Relations (PSIR)',
            'Psychology',
            'Social Anthropology',
            'Sociology',
            'Special Needs and Inclusive Education',
            'Sport Science',
            'Statistics',
            'Theatrical Arts & Music',
        ],
    ];

    /**
     * Flat sorted array of all canonical departments without duplicates
     */
    public static function all(): array
    {
        $all = [];
        foreach (self::DIVISIONS as $depts) {
            foreach ($depts as $d) {
                $all[] = $d;
            }
        }
        $unique = array_values(array_unique($all));
        sort($unique, SORT_NATURAL | SORT_FLAG_CASE);
        return $unique;
    }

    /**
     * Map old, loose, typo or case-differing department names to their canonical form
     */
    public static function normalize(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);
        if ($trimmed === '') {
            return null;
        }

        $synonyms = [
            'accounting' => 'Accounting & Finance',
            'accounting & finance' => 'Accounting & Finance',
            'accounting and finance' => 'Accounting & Finance',
            'computer science' => 'Computer Science',
            'computerscience' => 'Computer Science',
            'cs' => 'Computer Science',
            'mathematics' => 'Mathematics',
            'math' => 'Mathematics',
            'management' => 'Management',
            'jornal' => 'Journalism & Communication',
            'journalism' => 'Journalism & Communication',
            'journalism & communication' => 'Journalism & Communication',
            'electrical engineering' => 'Electrical & Computer Engineering',
            'electrical & computer engineering' => 'Electrical & Computer Engineering',
            'medicine' => 'Medicine',
            'software engineering' => 'Software Engineering',
            'information technology' => 'Information Technology',
            'economics' => 'Economics',
            'civil engineering' => 'Civil Engineering',
            'mechanical engineering' => 'Mechanical Engineering',
            'chemical engineering' => 'Chemical Engineering',
            'marketing management' => 'Marketing Management',
            'pharmacy' => 'Pharmacy',
            'nursing' => 'Nursing',
            'public health' => 'Public Health',
            'law' => 'Law',
            'architecture' => 'Architecture',
            'physics' => 'Physics',
            'chemistry' => 'Chemistry',
            'biology' => 'Biology',
            'statistics' => 'Statistics',
            'cotm' => 'Construction Technology & Management (COTM)',
            'padm' => 'Public Administration and Development Management (PADM)',
            'dvm' => 'Veterinary Medicine (DVM)',
            'mls' => 'Medical Laboratory Sciences (MLS)',
            'psir' => 'Political Science and International Relations (PSIR)',
            'nrm' => 'Natural Resource Management (NRM)',
            'rdae' => 'Rural Development and Agricultural Extension (RDAE)',
            'gis' => 'Geographic Information Systems (GIS) and Remote Sensing',
        ];

        $lower = strtolower($trimmed);
        if (isset($synonyms[$lower])) {
            return $synonyms[$lower];
        }

        foreach (self::all() as $canonical) {
            if (strcasecmp($canonical, $trimmed) === 0) {
                return $canonical;
            }
        }

        return $trimmed;
    }
}
