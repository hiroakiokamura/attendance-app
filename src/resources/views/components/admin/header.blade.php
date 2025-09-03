<!-- 管理者用ヘッダー -->
<header class="bg-black text-white py-4">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <!-- COACHTECHロゴ -->
            <div class="flex items-center">
                <a href="{{ route('admin.attendance.list') }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logos/coachtech-logo.svg') }}" 
                         alt="COACHTECH" 
                         class="h-8 w-auto"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <!-- フォールバック用ロゴ（SVGが読み込めない場合） -->
                    <div class="items-center" style="display: none;">
                        <div class="bg-white text-black px-2 py-1 rounded mr-2 font-bold text-sm">
                            CT
                        </div>
                        <span class="text-xl font-bold">COACHTECH</span>
                    </div>
                </a>
            </div>

            <!-- 管理者メニュー（ログイン後のみ表示） -->
            @auth
                <nav class="hidden md:flex space-x-6">
                    <a href="{{ route('admin.attendance.list') }}" 
                       class="hover:text-gray-300 transition-colors {{ request()->routeIs('admin.attendance.*') ? 'text-gray-300' : '' }}">
                        勤怠一覧
                    </a>
                    <a href="{{ route('admin.staff.list') }}" 
                       class="hover:text-gray-300 transition-colors {{ request()->routeIs('admin.staff.*') ? 'text-gray-300' : '' }}">
                        スタッフ一覧
                    </a>
                    <a href="{{ route('admin.stamp_correction_request.list') }}" 
                       class="hover:text-gray-300 transition-colors {{ request()->routeIs('admin.stamp_correction_request.*') ? 'text-gray-300' : '' }}">
                        申請一覧
                    </a>
                </nav>

                <!-- ログアウト -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-300">管理者</span>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm hover:text-gray-300 transition-colors">
                            ログアウト
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</header>
