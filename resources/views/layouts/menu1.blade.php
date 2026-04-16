@php
    $userId = session('UserID');
    $modulgrup = session('modulgrup');
    $menus = [];
    if($userId && $modulgrup > 0) {
        $menus = app(\App\Services\WelcomeService::class)->getSidebarMenu($userId, $modulgrup);
    }
@endphp
<div class="left-side-menu">

<div class="slimscroll-menu">
	<!--- Sidemenu -->
	<div id="sidebar-menu">

		<ul class="metismenu" id="side-menu">

			<li>
				<a href="{{ route('dashboard') }}" onclick='home();'><i class="mdi mdi-view-dashboard"></i><span> Beranda</span></a>
			</li>
            
            @foreach($menus as $menu)
                @php
                    // Hapus prefix 'c_' bawaan CI3 jika ada, misal 'c_level' menjadi 'level'
                    $menuScript = str_starts_with($menu['Script'], 'c_') ? substr($menu['Script'], 2) : $menu['Script'];
                @endphp
                <li>
                    <a href="{{ count($menu['Submenus']) > 0 ? 'javascript:void(0);' : url($menuScript) }}" 
                       class="menu_{{ $menu['ModulID'] }}" id="menu_{{ $menu['ModulID'] }}">
                        
                        <i class="mdi mdi-view-dashboard"></i>
                        <span> 
                            {{ \Illuminate\Support\Str::limit($menu['Nama'], 20) }} 
                            {!! $menu['BadgeVerif'] !!}
                        </span>

                        @if(count($menu['Submenus']) > 0)
                            <span class="menu-arrow"></span>
                        @endif

                    </a>

                    @if(count($menu['Submenus']) > 0)
                        <ul class="nav-second-level" aria-expanded="false">
                            @foreach($menu['Submenus'] as $sub)
                                @php
                                    $subScript = str_starts_with($sub['Script'], 'c_') ? substr($sub['Script'], 2) : $sub['Script'];
                                @endphp
                                <li>
                                    <a href="{{ url($subScript) }}" 
                                       class="submenu_{{ $sub['ModulID'] }}" id="submenu_{{ $sub['ModulID'] }}">
                                        {{ $sub['Nama'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach

		</ul>

	</div>

</div>

<div class="slimScrollBar" style="background: rgb(158, 165, 171); width: 8px; position: absolute; top: 27px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 44.8046px;"></div>
<div class="slimScrollRail" style="width: 8px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div>

<!-- Sidebar -left -->
</div>

@push('scripts')
<script>
$( document ).ready(function() {
	$("#side-menu").metisMenu();
	$(".slimscroll-menu").slimscroll({
		height: "547px",
		position: "right",
		size: "8px",
		color: "#9ea5ab",
		wheelStep: 5,
		touchScrollStep: 20
	});
});
</script>
@endpush
