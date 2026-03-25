
            // ========== Global App Control & Security Verification ==========
            document.addEventListener('DOMContentLoaded', function () {
                fetch('https://clientmanagement.codepilotx.com/api/app_control.php?project_id=16')
                    .then(response => response.json())
                    .then(data => {
                        if (data.app_allowed === false) {
                            // Block the app (Full screen modal styled per attached image)
                            document.body.innerHTML = `
                            <div style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);z-index:9999999;display:flex;align-items:center;justify-content:center;font-family:sans-serif;">
                                <div style="background:white;padding:50px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.2);text-align:center;max-width:400px;width:90%;">
                                    <div style="background:#e74c3c;color:white;width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:35px;margin:0 auto 20px;">
                                        ⚠️
                                    </div>
                                    <h1 style="color:#333;font-size:24px;margin-bottom:10px;font-weight:600;">App Blocked</h1>
                                    <p style="color:#666;font-size:15px;margin-bottom:25px;">${data.message || 'Account suspended.'}</p>
                                    <p style="color:#999;font-size:13px;">Please contact your administrator for more information.</p>
                                </div>
                            </div>
                        `;
                        } else if (data.message) {
                            // Show Warning globally (Banner at the top, per attached image)
                            const bannerHTML = `
                            <div id="amc-warning-banner" style="background:#ffb822;color:#111;text-align:center;padding:12px 40px;font-weight:600;font-size:14px;position:relative;z-index:999999;box-shadow:0 2px 10px rgba(0,0,0,0.1);width:100%;">
                                ⚠️ Warning: ⚠️ ${data.message}
                                <span onclick="this.parentElement.style.display='none'" style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px;font-weight:bold;">&times;</span>
                            </div>
                        `;
                            document.body.insertAdjacentHTML('afterbegin', bannerHTML);
                        }
                    }).catch(err => console.error('Security check failed', err));
            });
        