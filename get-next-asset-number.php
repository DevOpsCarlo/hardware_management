<?php

require_once 'db.php';

header('Content-Type: application/json');

try {
  $input = json_decode(file_get_contents('php://input'), true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON input');
  }

  $category_code = $input['category_code'] ?? '';
  $suffix = strtoupper($input['suffix'] ?? '');

  if (empty($category_code)) {
    throw new Exception('Category code is required');
  }

  $next_number = getNextAssetNumber($pdo, $category_code, $suffix);

  echo json_encode([
    'success' => true,
    'next_number' => $next_number
  ]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}

// function getNextAssetNumber($pdo, $category_code, $suffix = '')
// {
//   try {
//     if (empty($suffix)) {
//       // MAIN ASSET — only match asset numbers that are exactly in this format: TRA-01-0001
//       $stmt = $pdo->prepare("
//         SELECT asset_number 
//         FROM asset 
//         WHERE asset_number REGEXP CONCAT('^TRA-', ?, '-[0-9]{4}$')
//         ORDER BY 
//           CAST(SUBSTRING(asset_number, LENGTH(CONCAT('TRA-', ?, '-')) + 1) AS UNSIGNED) DESC
//         LIMIT 1
//       ");

//       $stmt->execute([$category_code, $category_code]);

//       $lastAssetNumber = $stmt->fetchColumn();

//       if ($lastAssetNumber) {
//         $prefix = "TRA-{$category_code}-";
//         $numberPart = str_replace($prefix, '', $lastAssetNumber);
//         $nextNumber = str_pad(((int)$numberPart + 1), 4, '0', STR_PAD_LEFT);
//       } else {
//         $nextNumber = '0001';
//       }
//     } else {
//       // PERIPHERAL — find most recent MAIN asset only (no suffix), and reuse that number
//       $stmt = $pdo->prepare("
//         SELECT asset_number 
//         FROM asset 
//         WHERE asset_number REGEXP CONCAT('^TRA-', ?, '-[0-9]{4}$')
//         ORDER BY 
//           CAST(SUBSTRING(asset_number, LENGTH(CONCAT('TRA-', ?, '-')) + 1) AS UNSIGNED) DESC
//         LIMIT 1
//       ");

//       $stmt->execute([$category_code, $category_code]);
//       $lastMainAsset = $stmt->fetchColumn();

//       if ($lastMainAsset) {
//         $prefix = "TRA-{$category_code}-";
//         $numberPart = str_replace($prefix, '', $lastMainAsset);
//         $nextNumber = $numberPart; // Use the same base number
//       } else {
//         $nextNumber = '0001'; // fallback if no base asset exists yet
//       }
//     }

//     return $nextNumber;
//   } catch (Exception $e) {
//     error_log("Error getting next asset number: " . $e->getMessage());
//     return '0001';
//   }
// }
function getNextAssetNumber($pdo, $category_code, $suffix = '')
{
  try {
    $prefix = "TRA-{$category_code}-";

    if (empty($suffix)) {
      // Main asset generation
      $stmt = $pdo->prepare("
        SELECT asset_number 
        FROM asset 
        WHERE asset_number REGEXP CONCAT('^TRA-', ?, '-[0-9]{4}$')
        ORDER BY 
          CAST(SUBSTRING(asset_number, LENGTH(CONCAT('TRA-', ?, '-')) + 1) AS UNSIGNED) DESC
        LIMIT 1
      ");
      $stmt->execute([$category_code, $category_code]);

      $lastAssetNumber = $stmt->fetchColumn();

      if ($lastAssetNumber) {
        $numberPart = (int)str_replace($prefix, '', $lastAssetNumber);
        $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
      } else {
        $nextNumber = '0001';
      }

      return $nextNumber;
    } else {
      // Peripheral generation with suffix check
      $baseNumber = 1;
      while (true) {
        $formattedNumber = str_pad($baseNumber, 4, '0', STR_PAD_LEFT);
        $candidate = "{$prefix}{$formattedNumber}{$suffix}";

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM asset WHERE asset_number = ?");
        $stmt->execute([$candidate]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
          return $formattedNumber;
        }

        $baseNumber++;
      }
    }
  } catch (Exception $e) {
    error_log("Error generating asset number: " . $e->getMessage());
    return '0001';
  }
}
