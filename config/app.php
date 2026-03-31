<?php
/**
 * Application Configuration
 * SDO-BACtrack - BAC Procedural Timeline Tracking System
 */

// Application settings
define('APP_NAME', 'SDO-BACtrack');
define('APP_TITLE', 'SDO-BACtrack');
define('APP_SUBTITLE', 'Procurement, Bids and Awards Committee Timeline Tracker');
define('APP_VERSION', '1.0.0');
define('APP_URL', '/SDO-BACtrack');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
// Project owner documents: broader set (any kinds)
define('PROJECT_OWNER_ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'rar', 'csv']);

// Session settings
define('SESSION_NAME', 'sdo_bactrack_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('AUTH_TOKEN_PARAM', 'auth_token');

// Notification settings
define('DEADLINE_WARNING_DAYS', 2);

// Modes of Procurement
define('PROCUREMENT_TYPES', [
    'PUBLIC_BIDDING'              => 'Public Bidding',
    'LIMITED_SOURCE_BIDDING'      => 'Limited Source Bidding (Selective Bidding)',
    'COMPETITIVE_DIALOGUE'        => 'Competitive Dialogue',
    'UNSOLICITED_OFFER'           => 'Unsolicited Offer with Bid Matching',
    'DIRECT_CONTRACTING'          => 'Direct Contracting (Single Source Procurement)',
    'DIRECT_ACQUISITION'          => 'Direct Acquisition',
    'REPEAT_ORDER'                => 'Repeat Order',
    'SMALL_VALUE_PROCUREMENT_200K' => 'Small Value Procurement (200k & Above)',
    'SMALL_VALUE_PROCUREMENT'     => 'Small Value Procurement (200k & Below)',
    'NEGOTIATED_PROCUREMENT'      => 'Negotiated Procurement',
    'COMPETITIVE_BIDDING'         => 'Competitive Bidding',
    'DIRECT_SALES'                => 'Direct Sales',
    'DIRECT_PROCUREMENT_STI'      => 'Direct Procurement for Science, Technology, and Innovation',
    'CONSULTING_SERVICES'         => 'Consulting Services',
]);

// Project activity statuses
define('ACTIVITY_STATUSES', [
    'PENDING' => 'Pending',
    'IN_PROGRESS' => 'In Progress',
    'COMPLETED' => 'Completed',
    'DELAYED' => 'Delayed'
]);

// Compliance statuses
define('COMPLIANCE_STATUSES', [
    'COMPLIANT' => 'Compliant',
    'NON_COMPLIANT' => 'Non-Compliant'
]);

// User roles
define('USER_ROLES', [
    'PROJECT_OWNER'  => 'Project Owner',
    'PROCUREMENT'    => 'BAC Member',
    'BAC_CHAIRMAN'   => 'BAC Chairman',
    'BAC_SECRETARY'  => 'BAC Secretary',
    'SUPERADMIN'     => 'Superadmin'
]);

// Project approval statuses
define('PROJECT_APPROVAL_STATUSES', [
    'DRAFT' => 'Draft',
    'PENDING_APPROVAL' => 'Pending Approval',
    'APPROVED' => 'Approved',
    'REJECTED' => 'Rejected'
]);

// BAC Cycle statuses
define('CYCLE_STATUSES', [
    'ACTIVE' => 'Active',
    'COMPLETED' => 'Completed',
    'CANCELLED' => 'Cancelled'
]);

// Action types for history logs
define('ACTION_TYPES', [
    'DATE_CHANGE' => 'Date Change',
    'STATUS_CHANGE' => 'Status Change',
    'COMPLIANCE_TAG' => 'Compliance Tag'
]);

// Timezone
date_default_timezone_set('Asia/Manila');
