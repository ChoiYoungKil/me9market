<?php

// Nginx/PHP may resolve the public/admin directory before Laravel's /admin route.
// Authentication and role checks stay on the dashboard route.
header('Location: /admin/dashboard', true, 302);
exit;
