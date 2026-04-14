<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Vietnamese strings for local_notification_manager.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Quản lý Thông báo';
$string['dashboardtitle'] = 'Tổng quan';
$string['searchusers'] = 'Tìm kiếm người dùng...';
$string['pleaseselectuser'] = 'Vui lòng chọn hoặc tìm kiếm người dùng để xem thông báo.';
$string['notificationsfor'] = 'Thông báo của {$a}';
$string['search'] = 'Tìm kiếm';
$string['filter_all'] = 'Tất cả';
$string['filter_read'] = 'Đã đọc';
$string['filter_unread'] = 'Chưa đọc';
$string['col_subject'] = 'Tiêu đề';
$string['col_message'] = 'Nội dung';
$string['col_component'] = 'Thành phần';
$string['col_timecreated'] = 'Thời gian tạo';
$string['col_timeread'] = 'Thời gian đọc';
$string['delete_selected'] = 'Xóa đã chọn';
$string['confirm_delete'] = 'Bạn có chắc chắn muốn xóa các thông báo đã chọn? Hành động này không thể hoàn tác.';
$string['no_notifications'] = 'Không có thông báo nào.';
$string['error_permission'] = 'Bạn không có quyền quản lý thông báo.';
$string['success_deleted'] = 'Đã xóa thành công {$a} thông báo.';
$string['error_delete'] = 'Đã xảy ra lỗi khi xóa thông báo.';
$string['notification_manager:manage'] = 'Danh sách thông báo';
$string['select_all'] = 'Chọn tất cả';
$string['tab_dashboard'] = 'Tổng quan';
$string['tab_manage'] = 'Danh sách thông báo';
$string['time_range'] = 'Khoảng thời gian';
$string['time_7_days'] = '7 ngày qua';
$string['time_30_days'] = '30 ngày qua';
$string['time_90_days'] = '90 ngày qua';
$string['time_all'] = 'Tất cả';
$string['analytic_engagement'] = 'Tỷ lệ tương tác';
$string['analytic_unread_rate'] = 'Tỷ lệ chưa đọc';
$string['analytic_total'] = 'Tổng số thông báo';
$string['analytic_read'] = 'Đã đọc';
$string['analytic_unread'] = 'Chưa đọc';
$string['analytic_top_users'] = 'Người dùng nhận nhiều thông báo nhất';
$string['analytic_popular_types'] = 'Loại thông báo phổ biến';
$string['tab_trash'] = 'Thùng rác';
$string['move_to_trash'] = 'Chuyển vào thùng rác';
$string['delete_permanently'] = 'Xóa vĩnh viễn';
$string['delete_selected_permanently'] = 'Xóa vĩnh viễn mục đã chọn';
$string['restore_selected'] = 'Khôi phục mục đã chọn';
$string['col_timedeleted'] = 'Thời gian xóa';
$string['no_trash_notifications'] = 'Không có thông báo nào trong thùng rác.';
$string['success_trashed'] = 'Đã chuyển thành công {$a} thông báo vào thùng rác.';
$string['success_restored'] = 'Đã khôi phục thành công {$a} thông báo.';
$string['confirm_move_trash'] = 'Bạn có chắc muốn chuyển các thông báo đã chọn vào thùng rác?';
$string['confirm_delete_permanently'] = 'Bạn có chắc muốn XÓA VĨNH VIỄN các thông báo đã chọn? Không thể khôi phục lại.';
$string['confirm_restore'] = 'Bạn có chắc muốn khôi phục các thông báo đã chọn?';
$string['cleanup_trash_task'] = 'Dọn dẹp các thông báo cũ khỏi thùng rác';
$string['invalid_user_id'] = 'ID người dùng không hợp lệ.';
$string['no_notifications_selected'] = 'Chưa chọn thông báo nào.';
$string['unknown_action'] = 'Hành động không xác định.';
$string['no_matching_notifications'] = 'Không tìm thấy thông báo nào phù hợp cho người dùng này.';

// Privacy strings.
$string['privacy:metadata:trash'] = 'Thùng rác quản lý thông báo lưu trữ thông tin chi tiết về các thông báo đã bị xóa tạm để có thể khôi phục lại.';
$string['privacy:metadata:trash:useridto'] = 'ID của người dùng mà thông báo đã được gửi đến.';
$string['privacy:metadata:trash:subject'] = 'Tiêu đề của thông báo.';
$string['privacy:metadata:trash:component'] = 'Thành phần Moodle đã tạo ra thông báo.';
$string['privacy:metadata:trash:timecreated'] = 'Thời gian thông báo được tạo ban đầu.';
$string['privacy:metadata:trash:timedeleted'] = 'Thời gian thông báo bị xóa tạm vào thùng rác.';
