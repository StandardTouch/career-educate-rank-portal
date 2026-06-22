<?php

namespace App\Http\Controllers;

class NeetAnalysisController extends Controller
{
    public function show()
    {
        $overview = [
            ['label' => 'Candidates Registered', 'value' => 2276069, 'note' => 'NEET UG 2025'],
            ['label' => 'Exam Centres', 'value' => 5468, 'note' => 'Infrastructure coverage'],
            ['label' => 'Cities Covered', 'value' => 566, 'note' => 'Including 14 cities outside India'],
            ['label' => 'Female Candidates', 'value' => 1310062, 'note' => 'Gender highlights'],
        ];

        $neetOverviewRows = [
            ['sl' => '01', 'particular' => 'No. of Candidates registered', 'count' => '22,76,069'],
            ['sl' => '02', 'particular' => 'Male Candidates', 'count' => '9,65,996'],
            ['sl' => '03', 'particular' => 'Female Candidates', 'count' => '13,10,062'],
            ['sl' => '04', 'particular' => 'Transgender Candidates', 'count' => '11'],
            ['sl' => '05', 'particular' => 'Indian Nationals', 'count' => '22,73,528'],
            ['sl' => '06', 'particular' => 'NRIs', 'count' => '741'],
            ['sl' => '07', 'particular' => 'OCIs', 'count' => '861'],
            ['sl' => '08', 'particular' => 'Foreigners', 'count' => '939'],
            ['sl' => '09', 'particular' => 'Number of Centres', 'count' => '5,468'],
            ['sl' => '10', 'particular' => 'Number of Cities', 'count' => '566 (including 14 cities outside India)'],
        ];

        $genderDistribution = [
            ['label' => 'Male Candidates', 'value' => 965996],
            ['label' => 'Female Candidates', 'value' => 1310062],
            ['label' => 'Transgender Candidates', 'value' => 11],
        ];

        $nationalityBreakdown = [
            ['label' => 'Indian Nationals', 'value' => 2273528],
            ['label' => 'NRIs', 'value' => 741],
            ['label' => 'OCIs', 'value' => 861],
            ['label' => 'Foreigners', 'value' => 939],
        ];

        $categoryFunnel = [
            ['category' => 'GENERAL', 'registered' => 689366, 'appeared' => 665853, 'qualified' => 338728],
            ['category' => 'OBC', 'registered' => 948507, 'appeared' => 925739, 'qualified' => 564611],
            ['category' => 'SC', 'registered' => 333646, 'appeared' => 322538, 'qualified' => 168873],
            ['category' => 'ST', 'registered' => 150224, 'appeared' => 143602, 'qualified' => 67234],
            ['category' => 'EWS', 'registered' => 154326, 'appeared' => 151586, 'qualified' => 97085],
            ['category' => 'Total', 'registered' => 2276069, 'appeared' => 2209318, 'qualified' => 1236531],
        ];

        $yearAnalysis = [
            ['sl' => '1', 'metric' => 'No. of Candidates Registered', '2023' => '20,87,462', '2024' => '24,06,079', '2025' => '22,76,069', 'shift' => '-5.40%'],
            ['sl' => '2', 'metric' => 'No of Candidates Present', '2023' => '20,38,596', '2024' => '23,33,297', '2025' => '22,09,318', 'shift' => '-5.31%'],
            ['sl' => '3', 'metric' => 'No of Candidates Absent', '2023' => '48,866', '2024' => '72,782', '2025' => '66,751', 'shift' => '-8.29%'],
            ['sl' => '4', 'metric' => 'No of Candidates Qualified', '2023' => '11,45,976', '2024' => '13,15,853', '2025' => '12,36,531', 'shift' => '-6.03%'],
            ['sl' => '5', 'metric' => 'Male', '2023' => '9,02,936', '2024' => '10,29,198', '2025' => '9,65,996', 'shift' => '13.98%'],
            ['sl' => '6', 'metric' => 'Female', '2023' => '11,84,513', '2024' => '13,76,863', '2025' => '13,10,062', 'shift' => '-4.85%'],
            ['sl' => '7', 'metric' => 'Transgender', '2023' => '13', '2024' => '18', '2025' => '11', 'shift' => '-38.89%'],
            ['sl' => '8', 'metric' => 'Indian Nationals', '2023' => '20,36,316', '2024' => '24,02,774', '2025' => '22,73,528', 'shift' => '-5.38%'],
            ['sl' => '9', 'metric' => 'NRIs', '2023' => '852', '2024' => '1304', '2025' => '741', 'shift' => '-43.17%'],
            ['sl' => '10', 'metric' => 'OCIs', '2023' => '642', '2024' => '805', '2025' => '861', 'shift' => '06.96%'],
            ['sl' => '11', 'metric' => 'PIO', '2023' => '-', '2024' => '-', '2025' => '-', 'shift' => '-'],
            ['sl' => '12', 'metric' => 'Foreigners', '2023' => '786', '2024' => '1196', '2025' => '939', 'shift' => '-21.46%'],
            ['sl' => '13', 'metric' => 'Un Reserved', '2023' => '5,92,110', '2024' => '6,47,260', '2025' => '6,89,366', 'shift' => '6.51%'],
            ['sl' => '14', 'metric' => 'EWS', '2023' => '1,52,197', '2024' => '1,90,700', '2025' => '1,54,326', 'shift' => '-19.07%'],
            ['sl' => '15', 'metric' => 'OBC', '2023' => '8,73,173', '2024' => '10,54,277', '2025' => '9,48,507', 'shift' => '-10.03%'],
            ['sl' => '16', 'metric' => 'SC', '2023' => '2,94,995', '2024' => '3,56,727', '2025' => '3,33,646', 'shift' => '-6.47%'],
            ['sl' => '17', 'metric' => 'ST', '2023' => '1,26,121', '2024' => '1,57,115', '2025' => '1,50,224', 'shift' => '-4.39%'],
            ['sl' => '18', 'metric' => 'Number of Cities', '2023' => '499 (including 14 cities outside India)', '2024' => '571 (including 14 cities outside India)', '2025' => '566 (including 14 cities outside India)', 'shift' => '-5%'],
            ['sl' => '19', 'metric' => 'Number of Centres', '2023' => '4,097', '2024' => '4,750', '2025' => '5,468', 'shift' => '15.12%'],
            ['sl' => '20', 'metric' => 'Number of Languages', '2023' => '13', '2024' => '13', '2025' => '13', 'shift' => '0'],
            ['sl' => '21', 'metric' => 'Number of Center/Deputy Center Superintendent', '2023' => '4,097/7,826', '2024' => '4,750/6,520', '2025' => '5,468/8,205', 'shift' => '-'],
            ['sl' => '22', 'metric' => 'Number of Invigilators', '2023' => '1,73,954', '2024' => '2,10,105', '2025' => '1,89,672', 'shift' => '-9.73%'],
            ['sl' => '23', 'metric' => 'Number of Observers', '2023' => '5,804', '2024' => '7,500', '2025' => '10,936', 'shift' => '45.81%'],
            ['sl' => '24', 'metric' => 'No of City Coordinators', '2023' => '528', '2024' => '587', '2025' => '697', 'shift' => '18.74%'],
        ];

        $qualifiedPerformance = [
            ['sl' => '01', 'category' => 'UR/EWS', 'criteria' => '50th Percentile', 'marks' => '686 - 144', 'qualified' => '11,01,151'],
            ['sl' => '02', 'category' => 'OBC', 'criteria' => '40th Percentile', 'marks' => '143 - 113', 'qualified' => '88,692'],
            ['sl' => '03', 'category' => 'SC', 'criteria' => '40th Percentile', 'marks' => '143 - 113', 'qualified' => '31,995'],
            ['sl' => '04', 'category' => 'ST', 'criteria' => '40th Percentile', 'marks' => '143 - 113', 'qualified' => '13,940'],
            ['sl' => '05', 'category' => 'UR/EWS & PH', 'criteria' => '45th Percentile', 'marks' => '143 - 127', 'qualified' => '472'],
            ['sl' => '06', 'category' => 'OBC & PH', 'criteria' => '40th Percentile', 'marks' => '126 - 113', 'qualified' => '216'],
            ['sl' => '07', 'category' => 'SC & PH', 'criteria' => '40th Percentile', 'marks' => '126 - 113', 'qualified' => '48'],
            ['sl' => '08', 'category' => 'ST & PH', 'criteria' => '40th Percentile', 'marks' => '126 - 113', 'qualified' => '17'],
        ];

        $medicalSeats = [
            ['sl' => 1, 'state' => 'Andaman and Nicobar Islands', '2024' => 114, '2025' => 114, 'increase' => 0],
            ['sl' => 2, 'state' => 'Andhra Pradesh', '2024' => 6785, '2025' => 7215, 'increase' => 430],
            ['sl' => 3, 'state' => 'Arunachal Pradesh', '2024' => 100, '2025' => 100, 'increase' => 0],
            ['sl' => 4, 'state' => 'Assam', '2024' => 1700, '2025' => 1975, 'increase' => 275],
            ['sl' => 5, 'state' => 'Bihar', '2024' => 2995, '2025' => 3545, 'increase' => 550],
            ['sl' => 6, 'state' => 'Chandigarh', '2024' => 150, '2025' => 150, 'increase' => 0],
            ['sl' => 7, 'state' => 'Chhattisgarh', '2024' => 2255, '2025' => 2455, 'increase' => 200],
            ['sl' => 8, 'state' => 'Dadra and Nagar Haveli', '2024' => 177, '2025' => 177, 'increase' => 0],
            ['sl' => 9, 'state' => 'Delhi', '2024' => 1497, '2025' => 1396, 'increase' => -101],
            ['sl' => 10, 'state' => 'Goa', '2024' => 200, '2025' => 200, 'increase' => 0],
            ['sl' => 11, 'state' => 'Gujarat', '2024' => 7250, '2025' => 7525, 'increase' => 275],
            ['sl' => 12, 'state' => 'Haryana', '2024' => 2185, '2025' => 2710, 'increase' => 525],
            ['sl' => 13, 'state' => 'Himachal Pradesh', '2024' => 970, '2025' => 970, 'increase' => 0],
            ['sl' => 14, 'state' => 'Jammu and Kashmir', '2024' => 1385, '2025' => 1726, 'increase' => 341],
            ['sl' => 15, 'state' => 'Jharkhand', '2024' => 1055, '2025' => 1255, 'increase' => 200],
            ['sl' => 16, 'state' => 'Karnataka', '2024' => 12395, '2025' => 13944, 'increase' => 1549],
            ['sl' => 17, 'state' => 'Kerala', '2024' => 4755, '2025' => 5404, 'increase' => 649],
            ['sl' => 18, 'state' => 'Madhya Pradesh', '2024' => 5200, '2025' => 5725, 'increase' => 525],
            ['sl' => 19, 'state' => 'Maharashtra', '2024' => 11845, '2025' => 12824, 'increase' => 979],
            ['sl' => 20, 'state' => 'Manipur', '2024' => 525, '2025' => 525, 'increase' => 0],
            ['sl' => 21, 'state' => 'Meghalaya', '2024' => 200, '2025' => 200, 'increase' => 0],
            ['sl' => 22, 'state' => 'Mizoram', '2024' => 100, '2025' => 100, 'increase' => 0],
            ['sl' => 23, 'state' => 'Nagaland', '2024' => 100, '2025' => 100, 'increase' => 0],
            ['sl' => 24, 'state' => 'Odisha', '2024' => 2725, '2025' => 3025, 'increase' => 300],
            ['sl' => 25, 'state' => 'Puducherry', '2024' => 1873, '2025' => 1873, 'increase' => 0],
            ['sl' => 26, 'state' => 'Punjab', '2024' => 1700, '2025' => 1899, 'increase' => 199],
            ['sl' => 27, 'state' => 'Rajasthan', '2024' => 6505, '2025' => 7330, 'increase' => 825],
            ['sl' => 28, 'state' => 'Sikkim', '2024' => 150, '2025' => 150, 'increase' => 0],
            ['sl' => 29, 'state' => 'Tamil Nadu', '2024' => 12050, '2025' => 13050, 'increase' => 1000],
            ['sl' => 30, 'state' => 'Telangana', '2024' => 9065, '2025' => 9540, 'increase' => 475],
            ['sl' => 31, 'state' => 'Tripura', '2024' => 400, '2025' => 450, 'increase' => 50],
            ['sl' => 32, 'state' => 'Uttar Pradesh', '2024' => 12475, '2025' => 13425, 'increase' => 950],
            ['sl' => 33, 'state' => 'Uttarakhand', '2024' => 1350, '2025' => 1450, 'increase' => 100],
            ['sl' => 34, 'state' => 'West Bengal', '2024' => 5700, '2025' => 6499, 'increase' => 799],
            ['sl' => 35, 'state' => 'Grand Total', '2024' => 117931, '2025' => 129026, 'increase' => 11095],
        ];

        $topCollegeStates = [
            ['state' => 'Uttar Pradesh', 'colleges' => 88],
            ['state' => 'Maharashtra', 'colleges' => 85],
            ['state' => 'Tamil Nadu', 'colleges' => 78],
            ['state' => 'Karnataka', 'colleges' => 72],
            ['state' => 'Telangana', 'colleges' => 66],
        ];

        $collegeTypes = [
            ['type' => 'Government', '2024' => 398, '2025' => 421],
            ['type' => 'Private', '2024' => 297, '2025' => 315],
            ['type' => 'Deemed University', '2024' => 57, '2025' => 59],
            ['type' => 'AIIMS/JIPMER/CU/AFMC', '2024' => 25, '2025' => 25],
        ];

        return view('neet-analysis.show', compact(
            'overview',
            'neetOverviewRows',
            'genderDistribution',
            'nationalityBreakdown',
            'categoryFunnel',
            'yearAnalysis',
            'qualifiedPerformance',
            'medicalSeats',
            'topCollegeStates',
            'collegeTypes'
        ));
    }
}
