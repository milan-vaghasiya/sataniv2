<?php $this->load->view('app/includes/header'); ?>
<!-- Header -->
<header class="header">
	<div class="main-bar bg-primary-2">
		<div class="container">
			<div class="header-content">
				<div class="left-content">
					<a href="javascript:void(0);" class="menu-toggler me-2">
						<svg class="text-dark" xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 0 24 24" width="30px" fill="#000000"><path d="M13 14v6c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1h-6c-.55 0-1 .45-1 1zm-9 7h6c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1zM3 4v6c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1zm12.95-1.6L11.7 6.64c-.39.39-.39 1.02 0 1.41l4.25 4.25c.39.39 1.02.39 1.41 0l4.25-4.25c.39-.39.39-1.02 0-1.41L17.37 2.4c-.39-.39-1.03-.39-1.42 0z"></path></svg>
					</a>
					<h5 class="title mb-0 text-nowrap"><?=$headData->pageTitle?></h5>
				</div>
				<div class="mid-content"> </div>
				<div class="right-content headerSearch">
					<div class="jpsearch" id="qs1">
						<input type="text" class="input quicksearch qs1" placeholder="Search Here ..." />
						<button class="search-btn"><i class="fas fa-search"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
<!-- Header -->
	
<!-- Page Content -->
<div class="page-content">
	<div class="content-inner pt-0">
		<div class="container bottom-content">
			<div class="dz-tab style-4">
				<div class="tab-slide-effect">
					<ul class="nav nav-tabs" role="tablist">
						<li class="tab-active-indicator " style="width: 108.391px; transform: translateX(177.625px);"></li>
						<li class="nav-item <?=(($month == 1) ? 'active' : '')?>" role="presentation">
							<a href="<?=base_url($headData->controller."/monthlyAttendanceSummary/1")?>" class="btn nav-link"> Current Month </a>
						</li>
						<li class="nav-item <?=(($month == 2) ? 'active' : '')?>" role="presentation">
							<a href="<?=base_url($headData->controller."/monthlyAttendanceSummary/2")?>" class="btn nav-link"> Last Month </a>
						</li>   
					</ul>
				</div>    
				<div class="tab-content px-0 list-grid" id="myTabContent1"  data-isotope='{ "itemSelector": ".listItem" }'>
					<ul class="dz-list message-list">
						<?=(!empty($attendData) ? $attendData : '')?>
					</ul>
				</div>
			</div>				
		</div>    
	</div>
</div>    
<!-- Page Content End-->

<?php $this->load->view('app/includes/bottom_menu'); ?>
<?php $this->load->view('app/includes/footer'); ?>
<?php $this->load->view('app/includes/sidebar'); ?>

<script src="<?=base_url()?>assets/plugins/isotop/isotope.pkgd.min.js"></script>
<script>
var qsRegex;
var isoOptions ={};
var $grid = '';
$(document).ready(function(){
    initISOTOP();
	var $qs = $('.quicksearch').keyup( debounce( function() {qsRegex = new RegExp( $qs.val(), 'gi' );$grid.isotope();}, 200 ) );
});

function searchItems(ele){
	console.log($(ele).val());
}

function debounce( fn, threshold ) {
  var timeout;
  threshold = threshold || 100;
  return function debounced() {
	clearTimeout( timeout );
	var args = arguments;
	var _this = this;
	
	function delayed() {fn.apply( _this, args );}
	timeout = setTimeout( delayed, threshold );
  };
}

function initISOTOP(){
    var isoOptions = {
		itemSelector: '.listItem',
		layoutMode: 'fitRows',
		filter: function() {return qsRegex ? $(this).text().match( qsRegex ) : true;}
	};
    $('.listItem').css('position', 'static');
	// init isotope
	$grid = $('.list-grid').isotope( isoOptions );
}
</script>