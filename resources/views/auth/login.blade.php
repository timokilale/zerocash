<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroCash Banking System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }
        .login-right {
            padding: 60px 40px;
        }
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .feature-item i {
            margin-right: 15px;
            font-size: 1.2rem;
        }
        .credentials-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .credentials-info h6 {
            color: #495057;
            margin-bottom: 15px;
        }
        .credential-item {
            background: white;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container row g-0">
            <!-- Left Side - Branding -->
            <div class="col-md-6 login-left">
                <div class="logo">
                    <i class="fas fa-university"></i> ZeroCash
                </div>
                <h3 class="mb-4">Banking System</h3>
                <p class="mb-4">Secure, Modern, and Feature-Rich Banking Platform</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure Authentication</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-money-bill-transfer"></i>
                        <span>Automatic Loan Disbursement</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calculator"></i>
                        <span>Smart Fee Calculation</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bell"></i>
                        <span>Real-time Notifications</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-id-card"></i>
                        <span>NIDA Integration</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Interest Rate Algorithm</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-md-6 login-right">
                <h2 class="mb-4 text-center">Welcome Back</h2>
                <p class="text-muted text-center mb-4">Please sign in to your account</p>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="{{ old('username') }}" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
                
                <!-- Demo Credentials -->
                <div class="credentials-info">
                    <h6><i class="fas fa-info-circle"></i> Demo Credentials</h6>
                    <div class="credential-item">
                        <strong>Root User:</strong> root / root123
                    </div>
                    <div class="credential-item">
                        <strong>CEO:</strong> ceo / 12345
                    </div>
                    <div class="credential-item">
                        <strong>Admin:</strong> admin / 1234
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
