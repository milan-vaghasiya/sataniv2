ALTER TABLE `prc_accept_log` ADD `remark` TEXT NULL DEFAULT NULL AFTER `review_id`;

--29-11-2025--
INSERT INTO `sub_menu_master` (`id`, `menu_type`, `sub_menu_seq`, `sub_menu_icon`, `sub_menu_name`, `sub_controller_name`, `menu_id`, `is_report`, `is_approve_req`, `is_system`, `report_id`, `notify_on`, `vou_name_long`, `vou_name_short`, `auto_start_no`, `vou_prefix`, `created_by`, `created_at`, `updated_by`, `updated_at`, `is_delete`, `cm_id`) VALUES (NULL, '1', '8', 'icon-Record', 'Services', 'items/list/8', '2', '0', '0', '0', NULL, '0,0,0', NULL, NULL, '0', NULL, '1', '2025-11-29 06:01:13', '0', '2025-11-29 06:01:13', '0', '0');
