<?php
session_start();

// Test admin logout
echo "<!DOCTYPE html>\n";
echo "<html><head><title>Admin Logout Test</title></head><body>\n";
echo "<h2>Admin Logout Test</h2>\n";

// Debug bilgileri
echo "<h3>Debug Bilgileri:</h3>\n";
echo "<p>Session ID: " . session_id() . "</p>\n";
echo "<p>Session Data: " . json_encode($_SESSION) . "</p>\n";
echo "<p>Cookies: " . json_encode($_COOKIE) . "</p>\n";

// Logout butonu
echo "<h3>Test Butonları:</h3>\n";
echo "<button onclick=\"window.location.href='/auth/logout'\">Auth Logout</button><br><br>\n";
echo "<button onclick=\"window.location.href='/admin/logout'\">Admin Logout</button><br><br>\n";
echo "<button onclick=\"performLogout()\">JavaScript performLogout</button><br><br>\n";

// performLogout fonksiyonu
echo "<script>\n";
echo "function performLogout() {\n";
echo "    console.log('performLogout çağrıldı');\n";
echo "    if (window.authGuard) {\n";
echo "        console.log('AuthGuard ile logout');\n";
echo "        window.authGuard.logout();\n";
echo "    } else {\n";
echo "        console.log('Direkt logout');\n";
echo "        window.location.href = '/auth/logout';\n";
echo "    }\n";
echo "}\n";
echo "</script>\n";

echo "</body></html>\n";
?>