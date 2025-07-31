<?php
// Session kontrolü (session zaten index.php'de başlatılmış)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Eğer kullanıcı zaten giriş yapmışsa anasayfaya yönlendir
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: /anasayfa');
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="Mağaza yönetim sistemi ile mağazanızı kolayca yönetin.">
	<meta name="author" content="Ünal">
	<title>Mağaza Takip - Hoş Geldiniz</title>
	
	<!-- PWA Meta Tags -->
	<meta name="theme-color" content="#1976d2">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="apple-mobile-web-app-title" content="Mağaza Takip">
	
	<!-- PWA Icons -->
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" href="/public/images/apple-touch-icon.png">
	<link rel="manifest" href="/public/manifest.json">
	
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	
	<style>
		.welcome-container {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		
		.welcome-card {
			background: rgba(255, 255, 255, 0.95);
			backdrop-filter: blur(10px);
			border-radius: 20px;
			padding: 3rem;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
			max-width: 500px;
			width: 90%;
			text-align: center;
		}
		
		.app-logo {
			width: 100px;
			height: 100px;
			margin: 0 auto 2rem;
			background: #fff;
			border-radius: 20px;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
		}
		
		.app-logo img {
			width: 60px;
			height: 60px;
		}
		
		.welcome-title {
			color: #2d3748;
			font-weight: 700;
			margin-bottom: 1rem;
			font-size: 2.5rem;
		}
		
		.welcome-subtitle {
			color: #4a5568;
			margin-bottom: 2rem;
			font-size: 1.1rem;
		}
		
		.login-btn {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			border-radius: 50px;
			padding: 1rem 3rem;
			font-weight: 600;
			font-size: 1.1rem;
			transition: all 0.3s ease;
			box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
		}
		
		.login-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
		}
		
		.features {
			margin-top: 2rem;
			text-align: left;
		}
		
		.feature-item {
			display: flex;
			align-items: center;
			margin-bottom: 0.75rem;
			color: #4a5568;
		}
		
		.feature-icon {
			width: 20px;
			height: 20px;
			margin-right: 0.75rem;
			color: #667eea;
		}
		
		/* Loading animation */
		.loading-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(255, 255, 255, 0.9);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 9999;
		}
		
		.auto-login-message {
			background: rgba(102, 126, 234, 0.1);
			border: 1px solid rgba(102, 126, 234, 0.3);
			border-radius: 10px;
			padding: 1rem;
			margin-top: 1rem;
			color: #667eea;
			display: none;
		}
	</style>
</head>
<body class="welcome-container">

	<!-- Loading Overlay -->
	<div class="loading-overlay" id="loadingOverlay">
		<div class="text-center">
			<div class="spinner-border text-primary mb-3" role="status">
				<span class="sr-only">Yükleniyor...</span>
			</div>
			<h5>Otomatik giriş yapılıyor...</h5>
		</div>
	</div>

	<!-- Welcome Card -->
	<div class="welcome-card">
		<!-- App Logo -->
		<div class="app-logo">
			<img src="/public/media/logos/default.svg" alt="Mağaza Takip Logo" />
		</div>
		
		<!-- Welcome Content -->
		<h1 class="welcome-title">Hoş Geldiniz!</h1>
		<p class="welcome-subtitle">Mağaza yönetim sistemi ile işletmenizi kolayca yönetin.</p>
		
		<!-- Auto Login Message -->
		<div class="auto-login-message" id="autoLoginMessage">
			<strong>Beni Hatırla</strong> aktif! Otomatik giriş yapılıyor...
		</div>
		
		<!-- Login Button -->
		<div class="mb-4">
			<a href="/auth/giris" class="btn btn-primary login-btn">
				Giriş Yap
			</a>
		</div>
		
		<!-- Features -->
		<div class="features">
			<div class="feature-item">
				<span class="feature-icon">📊</span>
				<span>Envanter Yönetimi</span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">👥</span>
				<span>Personel Takibi</span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">💰</span>
				<span>Satış Raporları</span>
			</div>
			<div class="feature-item">
				<span class="feature-icon">📱</span>
				<span>PWA Desteği</span>
			</div>
		</div>
	</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 Home page loaded, checking auto-login...');
    
    // "Beni Hatırla" kontrolü
    checkAutoLogin();
    
    function checkAutoLogin() {
        try {
            // LocalStorage'dan bilgileri al
            const rememberLogin = localStorage.getItem('remember_login');
            const savedEmail = localStorage.getItem('saved_email');
            const savedPassword = localStorage.getItem('saved_password');
            
            console.log('🔍 Remember login check:', {
                remember: !!rememberLogin,
                email: !!savedEmail,
                password: !!savedPassword
            });
            
            // Eğer "beni hatırla" aktifse ve bilgiler varsa
            if (rememberLogin === 'true' && savedEmail && savedPassword) {
                console.log('✅ Auto-login credentials found, attempting login...');
                
                // Auto-login mesajını göster
                showAutoLoginMessage();
                
                // 1 saniye bekleyip otomatik giriş yap
                setTimeout(() => {
                    performAutoLogin(savedEmail, savedPassword);
                }, 1000);
            } else {
                console.log('❌ No auto-login credentials found');
            }
            
        } catch (error) {
            console.error('❌ Auto-login check error:', error);
        }
    }
    
    function showAutoLoginMessage() {
        const message = document.getElementById('autoLoginMessage');
        const overlay = document.getElementById('loadingOverlay');
        
        if (message) {
            message.style.display = 'block';
        }
        
        // 500ms sonra loading overlay göster
        setTimeout(() => {
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }, 500);
    }
    
    async function performAutoLogin(email, password) {
        try {
            console.log('🔑 Performing auto-login for:', email);
            
            const formData = new FormData();
            formData.append('eposta', email);
            formData.append('sifre', password);
            formData.append('beni_hatirla', '1');
            
            const response = await fetch('/auth/giris', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                // Başarılı giriş
                console.log('✅ Auto-login successful, redirecting...');
                
                // Başarı mesajı göster
                updateLoadingMessage('✅ Giriş başarılı! Yönlendiriliyor...');
                
                // 1 saniye sonra anasayfaya yönlendir
                setTimeout(() => {
                    window.location.href = '/anasayfa';
                }, 1000);
                
            } else {
                console.error('❌ Auto-login failed:', response.status);
                hideAutoLoginElements();
                
                // Başarısız giriş durumunda remember_login'i temizle
                localStorage.removeItem('remember_login');
            }
            
        } catch (error) {
            console.error('❌ Auto-login error:', error);
            hideAutoLoginElements();
        }
    }
    
    function updateLoadingMessage(message) {
        const overlay = document.getElementById('loadingOverlay');
        const messageEl = overlay.querySelector('h5');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
    
    function hideAutoLoginElements() {
        const message = document.getElementById('autoLoginMessage');
        const overlay = document.getElementById('loadingOverlay');
        
        if (message) {
            message.style.display = 'none';
        }
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    // PWA Install Prompt
    let deferredPrompt;
    
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // PWA install banner göster
        showPWAInstallBanner();
    });
    
    function showPWAInstallBanner() {
        // Basit PWA install notification
        console.log('📱 PWA install prompt available');
    }
});
</script>

</body>
</html>
