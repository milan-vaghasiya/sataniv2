<div class="menubar-area style-5 footer-fixed">
	<div class="toolbar-inner menubar-nav">
		<a href="<?=base_url("app/dashboard")?>" data-page_url="app/dashboard" class="nav-link ">
			<div class="shape">
				<i class="fa-solid fa-house"></i>
				<div class="inner-shape"></div>
			</div>
			<span>Home</span>
		</a>
		<?=$this->permission->getEmployeeAppMenus(1)?>
		<a href="<?=base_url("app/employee/monthlyAttendanceSummary")?>" data-page_url="app/employee/monthlyAttendanceSummary" class="nav-link <?=($this->data['headData']->appMenu == 'monthlyAttendanceSummary') ? 'active' : ''?>">
			<div class="shape">
				<i class="fas fa-calendar-alt"></i>
				<div class="inner-shape"></div>
			</div>
			<span>Attendace Summary</span>
		</a>
	</div>
</div>