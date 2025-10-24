<?php
/**
 * QuizYourFaith Bulk Activation Code Generator
 * Generates 50 unique activation codes for email storage
 */

require_once __DIR__ . '/config/constants.php';

// Activation codes configuration
$codeTypes = ['premium', 'standard', 'trial'];
$expiresOptions = ['+1 year', '+2 years'];
$generatedCodes = [];

echo "=== QuizYourFaith Activation Code Generator ===\n\n";

// Generate 50 unique codes
for ($i = 1; $i <= 50; $i++) {
    $type = $codeTypes[array_rand($codeTypes)];
    $expires = $expiresOptions[array_rand($expiresOptions)];
    
    $code = 'QYF-' . date('Y') . '-' . strtoupper($type) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $expiryDate = date('Y-m-d', strtotime($expires));
    
    $generatedCodes[] = [
        'code' => $code,
        'type' => $type,
        'expires' => $expiryDate,
        'status' => 'VALID'
    ];
}

// Display codes in different formats
echo "GENERATED ACTIVATION CODES (50 codes)\n";
echo "=====================================\n\n";

echo "FORMAT 1: Simple List (for easy copying)\n";
echo "----------------------------------------\n";
foreach ($generatedCodes as $codeData) {
    echo $codeData['code'] . "\n";
}
echo "\n";

echo "FORMAT 2: Detailed List with Expiry\n";
echo "-----------------------------------\n";
foreach ($generatedCodes as $codeData) {
    echo sprintf(
        "%s | Type: %-8s | Expires: %s\n",
        $codeData['code'],
        $codeData['type'],
        $codeData['expires']
    );
}
echo "\n";

echo "FORMAT 3: JSON Format (for database import)\n";
echo "-------------------------------------------\n";
echo json_encode($generatedCodes, JSON_PRETTY_PRINT) . "\n\n";

echo "FORMAT 4: PHP Array Format (for config file)\n";
echo "--------------------------------------------\n";
echo "// Add these to your \\$activationCodes array in config/activation.php\n";
echo "\$activationCodes = [\n";
foreach ($generatedCodes as $codeData) {
    echo "    '{$codeData['code']}' => [\n";
    echo "        'expires' => '{$codeData['expires']}',\n";
    echo "        'type' => '{$codeData['type']}'\n";
    echo "    ],\n";
}
echo "];\n\n";

echo "FORMAT 5: CSV Format (for spreadsheet)\n";
echo "--------------------------------------\n";
echo "Code,Type,Expires,Status\n";
foreach ($generatedCodes as $codeData) {
    echo "{$codeData['code']},{$codeData['type']},{$codeData['expires']},{$codeData['status']}\n";
}
echo "\n";

// Create email-ready format
echo "EMAIL READY FORMAT:\n";
echo "==================\n\n";
echo "Subject: QuizYourFaith Activation Codes - Ready for Use\n\n";
echo "Dear User,\n\n";
echo "Here are your 50 QuizYourFaith activation codes. Each code can be used to activate\n";
echo "the software on a different server or installation.\n\n";
echo "IMPORTANT: Keep these codes secure and do not share them publicly.\n\n";
echo "ACTIVATION CODES:\n";
echo "----------------\n";
foreach ($generatedCodes as $index => $codeData) {
    echo sprintf("%2d. %s (Type: %s, Expires: %s)\n", 
        $index + 1, 
        $codeData['code'], 
        $codeData['type'], 
        $codeData['expires']
    );
}
echo "\n";
echo "USAGE INSTRUCTIONS:\n";
echo "1. Upload the QuizYourFaith files to your server\n";
echo "2. Navigate to your domain - you will be redirected to activation page\n";
echo "3. Enter one of the activation codes above\n";
echo "4. Follow the installation process\n";
echo "5. The software will be fully activated and ready to use\n\n";
echo "SUPPORT:\n";
echo "If you encounter any issues during activation, please contact support.\n\n";
echo "Best regards,\n";
echo "QuizYourFaith Team\n\n";

// Save to file for easy access
$filename = 'activation_codes_' . date('Y-m-d_H-i-s') . '.txt';
file_put_contents($filename, ob_get_clean() . ob_get_contents());
echo "\nCodes have been saved to: $filename\n";
echo "You can also copy any of the formats above directly from this output.\n\n";

echo "=== ACTIVATION CODE GENERATION COMPLETE ===\n";
echo "Total codes generated: " . count($generatedCodes) . "\n";
echo "Date generated: " . date('Y-m-d H:i:s') . "\n";
echo "Save this information in a secure location (email, password manager, etc.)\n";