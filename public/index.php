<?php
session_start();
$isAuthenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temp Mail</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px -1px rgba(0,0,0,0.1);
            --radius: 0.5rem;
            --transition: all 0.2s ease;
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.3);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Common */
        input, select, button {
            padding: 0.6rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--bg-card);
            color: var(--text-main);
            font-size: 0.9rem;
        }
        
        button {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }
        
        button:hover { opacity: 0.9; }
        
        button.secondary { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
        button.secondary:hover { background: var(--bg-hover); }
        button.danger { background: #ef4444; color: white; border: none; }
        button.danger:hover { background: #dc2626; }

        /* Auth Screen */
        #auth-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: var(--bg-body); z-index: 100;
        }
        .auth-card {
            background: var(--bg-card); padding: 2rem; border-radius: 1rem;
            box-shadow: var(--shadow); width: 100%; max-width: 320px; text-align: center;
        }

        /* App Header */
        header {
            background: var(--bg-card); border-bottom: 1px solid var(--border);
            padding: 0.75rem 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .logo { font-weight: 800; font-size: 1.25rem; color: var(--primary); }

        /* Top Controls Section */
        .controls-section {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        
        .control-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .control-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .separator { width: 1px; height: 2rem; background: var(--border); margin: 0 1rem; }
        
        .active-address-banner {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            padding: 1rem 2rem;
            border-radius: 999px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.25rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        
        [data-theme="dark"] .active-address-banner {
            color: #4ade80;
            background: rgba(34, 197, 94, 0.15);
        }

        /* Main Content */
        .main-container {
            max-width: 1000px;
            margin: 2rem auto;
            width: 90%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Email List Table */
        .email-list-container {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .email-table {
            width: 100%;
            border-collapse: collapse;
        }

        .email-table th {
            text-align: left;
            padding: 1rem;
            background: var(--bg-hover);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
        }

        .email-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .email-row { cursor: pointer; transition: background 0.2s; }
        .email-row:hover { background: var(--bg-hover); }
        .email-row.unread { background: rgba(79, 70, 229, 0.02); }
        .email-row.unread td:first-child { font-weight: bold; border-left: 3px solid var(--primary); }
        .email-row td:first-child { padding-left: calc(1rem - 3px); } /* compensate for border */

        /* Detail View */
        .detail-view {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        
        .detail-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .detail-body {
            flex: 1;
            background: white; /* Ensure correct rendering */
            position: relative;
        }
        
        iframe { width: 100%; height: 100%; border: none; }

        .hidden { display: none !important; }
        .flex { display: flex; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .spinner {
            border: 2px solid var(--text-muted);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }
        .view-header { display: flex; justify-content: space-between; margin-bottom: 1rem; align-items: center; }
        .accounts-controls { display: flex; gap: 1rem; margin-bottom: 1rem; justify-content: space-between; align-items: center; }
        .accounts-controls input { padding: 0.5rem; border: 1px solid var(--border); border-radius: 4px; width: 250px; }
        .pagination-controls { display: flex; gap: 0.5rem; align-items: center; }
        #account-page-info { font-size: 0.9rem; color: var(--text-muted); }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            /* Accounts View Mobile */
            .view-header, .accounts-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            .accounts-controls input {
                width: 100%;
                box-sizing: border-box;
            }
            .pagination-controls {
                justify-content: center;
            }
            
            header > div {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .controls-section {
                padding: 1rem;
            }

            .control-group {
                flex-direction: column;
                width: 100%;
            }

            .control-box {
                flex-direction: column;
                width: 100%;
                align-items: stretch;
            }

            .control-box > label {
                text-align: left;
            }
            .control-box > input, 
            .control-box > select,
            .control-box > button {
                width: 100%;
                min-width: 0;
            }

            .separator {
                display: none;
            }
            
            /* Make tables scrollable */
            .email-list-container {
                overflow-x: auto;
            }
            
            /* Adjust banner for mobile */
            .active-address-banner {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                width: 100%;
                box-sizing: border-box;
            }
            
            #active-display > div:last-child {
               flex-direction: column;
               width: 100%;
            }
            
            #active-display button {
                width: 100%;
            }

            /* Adjust Modal */
            .modal-content {
                width: 90% !important;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Auth -->
    <div id="auth-screen" style="<?php echo $isAuthenticated ? 'display:none' : ''; ?>">
        <div class="auth-card">
            <h2>Welcome</h2>
            <input type="password" id="pin-input" placeholder="Enter PIN" style="margin-bottom:1rem; width:100%" onkeyup="if(event.key === 'Enter') login()">
            <button onclick="login()" style="width:100%">Login</button>
            <p id="auth-error" style="color:#ef4444; display:none; margin-top:1rem">Invalid PIN</p>
        </div>
    </div>

    <!-- App -->
    <div id="dashboard" class="<?php echo $isAuthenticated ? '' : 'hidden'; ?>" style="flex-direction:column; width:100%; min-height:100vh; <?php echo $isAuthenticated ? 'display:flex' : ''; ?>">
        <header>
            <a href="/" class="logo" style="text-decoration:none;">TempMail</a>
            <div style="display:flex; gap:1rem; align-items:center;">
                <button class="secondary" onclick="checkImapStatus()">Check Status</button>
                <button class="secondary" onclick="showAccountsView()">All Emails</button>
                <button class="secondary" onclick="toggleTheme()">Theme</button>
                <button class="secondary" onclick="logout()">Logout</button>
            </div>
        </header>

        <!-- Main Workspace -->
        <div id="workspace" style="display:contents;">
            <!-- Controls (Center Top) -->
            <div class="controls-section">
                <div id="account-selector" class="control-group">
                    <div class="control-box">
                        <label style="font-size:0.8rem; font-weight:600; color:var(--text-muted)">Select Domain</label>
                        <select id="domain-select" style="min-width:150px;"></select>
                    </div>
                    
                    <div class="control-box">
                        <label style="font-size:0.8rem; font-weight:600; color:var(--text-muted)">Custom User</label>
                        <input type="text" id="custom-user" placeholder="username" style="width:120px;">
                        <button onclick="setAddress()">Set</button>
                    </div>
                    
                    <div class="separator"></div>
                    
                    <button class="secondary" onclick="generateRandom()">🎲 Generate Random</button>
                </div>

                <!-- Active Address Display -->
                <div id="active-display" class="hidden" style="flex-direction:column; align-items:center; gap:1rem;">
                    <div class="active-address-banner">
                        <span id="active-email"></span>
                        <button class="secondary" style="padding:0.2rem 0.5rem; font-size:0.75rem; border:none; background:rgba(255,255,255,0.2); color:inherit;" onclick="copyEmail()">Copy</button>
                    </div>
                    <div style="display:flex; gap:1rem;">
                        <button class="secondary" style="padding:0.4rem 0.8rem; font-size:0.8rem;" onclick="resetUser()">Change Address</button>
                        <button class="danger" style="padding:0.4rem 0.8rem; font-size:0.8rem;" onclick="deleteAccount()">Delete All Data</button>
                    </div>
                </div>
            </div>

            <div class="main-container">
                <!-- Loading State -->
                <div id="loading" class="hidden" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.5rem; color:var(--text-muted); font-size:0.9rem;">
                    <div class="spinner"></div>
                    <span>Syncing...</span>
                </div>

                <!-- List View -->
                <div id="inbox-view" class="email-list-container hidden">
                    <div style="padding:1rem; background:var(--bg-card); display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border);">
                        <h3 style="margin:0;">Inbox</h3>
                        <button class="secondary" onclick="sync()">Refresh</button>
                    </div>
                    <table class="email-table">
                        <thead>
                            <tr>
                                <th style="width: 25%">Sender</th>
                                <th style="width: 45%">Subject</th>
                                <th style="width: 20%">Time</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="email-tbody">
                            <!-- Rows -->
                        </tbody>
                    </table>
                    <div id="empty-inbox" style="padding:3rem; text-align:center; color:var(--text-muted); display:none;">
                        No messages yet.
                    </div>
                </div>

                <!-- Detail View -->
                <div id="detail-view" class="detail-view hidden">
                    <div class="detail-header">
                        <button class="secondary" onclick="showInbox()" style="margin-bottom:1rem;">&larr; Back to Inbox</button>
                        <h2 id="msg-subject" style="margin:0 0 0.5rem 0;"></h2>
                        <div style="display:flex; justify-content:space-between; color:var(--text-muted);">
                            <span id="msg-sender"></span>
                            <span id="msg-date"></span>
                        </div>
                    </div>
                    <div class="detail-body">
                        <iframe id="msg-frame"></iframe>
                    </div>
                </div>
                
                 <div id="landing-hero" style="text-align:center; padding:4rem; color:var(--text-muted);">
                    <h2>Waiting for input...</h2>
                    <p>Select a domain or generate a random address to start receiving mail.</p>
                </div>
            </div>
        </div>

        <!-- Accounts View -->
        <div id="accounts-view" class="main-container hidden">
            <div class="view-header">
                <h2 style="margin:0;">All Active Emails</h2>
                <button class="secondary" onclick="showDashboard(true)">Back to Workspace</button>
            </div>
            
            <div class="accounts-controls">
                <input type="text" id="account-search" placeholder="Search emails..." onkeyup="debounceLoadAccounts()">
                <div class="pagination-controls">
                    <button class="secondary" onclick="changeAccountPage(-1)">Prev</button>
                    <span id="account-page-info">Page 1</span>
                    <button class="secondary" onclick="changeAccountPage(1)">Next</button>
                </div>
            </div>

            <div class="email-list-container">
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Email Address</th>
                            <th>Messages</th>
                            <th>First Seen</th>
                            <th>Last Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="accounts-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- Status View -->
        <div id="status-view" class="main-container hidden">
             <div style="display:flex; justify-content:space-between; margin-bottom:1rem; align-items:center;">
                <h2 style="margin:0;">IMAP Connection Status</h2>
                <button class="secondary" onclick="showDashboard(true)">Back to Workspace</button>
            </div>
            
            <div id="status-loading" style="text-align:center; padding:2rem; color:var(--text-muted)">Checking connections...</div>
            
            <div class="email-list-container">
                 <table class="email-table" id="status-table" style="display:none;">
                    <thead><tr><th>Host</th><th>Email</th><th>Status</th><th>Details</th></tr></thead>
                    <tbody id="status-tbody"></tbody>
                 </table>
            </div>
        </div>

        <footer style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.85rem; margin-top:auto; border-top:1px solid var(--border);">
            <p style="margin:0 0 0.5rem 0;">Powered by Himel <?php echo date('Y'); ?></p>
             
        </footer>
    </div>
    
    <script>
        // ... (Existing scripts can stay, appending new logic)
        
        async function checkImapStatus() {
            // Switch view
            document.getElementById('workspace').style.display = 'none';
            document.getElementById('accounts-view').classList.add('hidden');
            const statusView = document.getElementById('status-view');
            statusView.classList.remove('hidden');
            
            // Reset UI
            const loading = document.getElementById('status-loading');
            const table = document.getElementById('status-table');
            const tbody = document.getElementById('status-tbody');
            
            loading.style.display = 'block';
            table.style.display = 'none';
            tbody.innerHTML = '';
            
            try {
                const res = await fetch('api.php?action=check_imap');
                const data = await res.json();
                
                loading.style.display = 'none';
                table.style.display = 'table';
                
                if (data.results) {
                    data.results.forEach(r => {
                        const color = r.status === 'OK' ? '#16a34a' : '#ef4444';
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.host}</td>
                            <td>${r.email}</td>
                            <td style="font-weight:bold; color:${color}">${r.status}</td>
                            <td style="font-size:0.8rem; color:var(--text-muted)">${r.status === 'OK' ? r.details : r.error}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                     tbody.innerHTML = '<tr><td colspan="4">No results or error.</td></tr>';
                }
            } catch (e) {
                loading.textContent = 'Error: ' + e.message;
            }
        }
    </script>
    <!-- End Script -->
    </div>


    <!-- Modal -->
    <div id="custom-modal" class="hidden" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:200;display:flex;align-items:center;justify-content:center;">
        <div class="modal-content" style="background:var(--bg-card);padding:2rem;border-radius:1rem;min-width:300px;max-width:400px;box-shadow:var(--shadow);">
            <h3 id="modal-title" style="margin-top:0;margin-bottom:1rem;">Title</h3>
            <p id="modal-msg" style="color:var(--text-muted);margin-bottom:1.5rem;"></p>
            <div style="display:flex;justify-content:flex-end;gap:1rem;">
                <button id="modal-cancel" class="secondary" onclick="closeModal()">Cancel</button>
                <button id="modal-confirm" onclick="confirmModalAction()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let currentEmail = localStorage.getItem('temp_email') || '';
        let refreshInterval = null;
        let currentModalAction = null;
        
        let accountPage = 1;
        let accountLimit = 10; // per user request or default
        let searchTimer = null;

        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            
            // Initial load for config if we're already viewing dashboard
            if (document.getElementById('dashboard').style.display !== 'none') {
                 loadDomains();
                 if(currentEmail) showActiveState();
                 else showLandingState();
            }
        });
        
        // ... (Modal logic stays same)
        function showModal(title, msg, onConfirm = null, isConfirm = false) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-msg').innerHTML = msg; 
            
            const cancelBtn = document.getElementById('modal-cancel');
            const confirmBtn = document.getElementById('modal-confirm');
            
            if (isConfirm) {
                cancelBtn.style.display = 'block';
                confirmBtn.style.display = 'block';
                if(onConfirm) currentModalAction = onConfirm;
                else currentModalAction = null;
            } else {
                cancelBtn.style.display = 'none';
                confirmBtn.style.display = 'block';
                confirmBtn.onclick = closeModal;
                confirmBtn.textContent = 'OK';
                currentModalAction = null;
            }
            
            document.getElementById('custom-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('custom-modal').classList.add('hidden');
            currentModalAction = null;
        }

        function confirmModalAction() {
            if (currentModalAction) currentModalAction();
            closeModal();
        }
        
        // ... (Existing loadDomains, toggleTheme etc... NO CHANGE)
        async function login() {
            const pin = document.getElementById('pin-input').value;
            const res = await fetch('api.php?action=login', {method:'POST',body:JSON.stringify({pin})});
            const data = await res.json();
            if(data.success) {
                location.reload(); 
            }
            else document.getElementById('auth-error').style.display = 'block';
        }

        async function logout() {
            await fetch('api.php?action=logout', {method:'POST'}).then(() => window.location.reload());
        }
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        }

        function showDashboard() {
            document.getElementById('auth-screen').style.display = 'none';
            const dash = document.getElementById('dashboard');
            dash.classList.remove('hidden');
            dash.style.display = 'flex';
            
            loadDomains();
            if(currentEmail) showActiveState();
            else showLandingState();
        }

        async function loadDomains() {
            try {
                const res = await fetch('api.php?action=domains');
                const data = await res.json();
                const select = document.getElementById('domain-select');
                select.innerHTML = '';
                (data.domains || []).forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = '@' + d;
                    select.appendChild(opt);
                });
            } catch(e) { console.error(e); }
        }

        function generateRandom() {
            const select = document.getElementById('domain-select');
            if(!select.value) return;
            const r = Math.random().toString(36).substring(7);
            const user = `temp${r}`; // updated to be simpler
            setCurrentEmail(`${user}@${select.value}`);
        }

        function setAddress() {
            const user = document.getElementById('custom-user').value.trim();
            const domain = document.getElementById('domain-select').value;
            if(!user || !domain) return;
            setCurrentEmail(`${user}@${domain}`);
        }

        function setCurrentEmail(e) {
            currentEmail = e;
            localStorage.setItem('temp_email', e);
            showActiveState();
        }
        
        function resetUser() {
            // Keep data but go back to landing
            showLandingState();
        }

        function showActiveState() {
            document.getElementById('account-selector').classList.add('hidden');
            document.getElementById('landing-hero').classList.add('hidden');
            
            const activeDisplay = document.getElementById('active-display');
            activeDisplay.classList.remove('hidden');
            activeDisplay.style.display = 'flex'; // Ensure flex
            
            document.getElementById('active-email').textContent = currentEmail;
            
            showInbox();
            sync(); // initial sync
            // Polling handled recursively within sync()
        }

        function showLandingState() {
            document.getElementById('account-selector').classList.remove('hidden');
            document.getElementById('active-display').classList.add('hidden');
            document.getElementById('inbox-view').classList.add('hidden');
            document.getElementById('landing-hero').classList.remove('hidden');
            if(refreshInterval) clearTimeout(refreshInterval);
        }

        function copyEmail() { 
            navigator.clipboard.writeText(currentEmail); 
            // Reuse nice button feedback or use modal
            showModal('Success', 'Email address copied to clipboard!');
        }

        function deleteAccount() {
            showModal('Delete Account', 'Are you sure you want to delete all messages and data for this address?', async () => {
                await fetch('api.php?action=delete_account', {method:'POST',body:JSON.stringify({email:currentEmail})});
                localStorage.removeItem('temp_email');
                currentEmail = '';
                showLandingState();
            }, true);
        }

        function showInbox() {
            document.getElementById('inbox-view').classList.remove('hidden');
            document.getElementById('detail-view').classList.add('hidden');
            renderInbox([]); // Clear first or keep old?
            refreshMessages();
        }

        let isSyncing = false;
        async function sync() {
            if(!currentEmail) return;
            // Prevent overlap if called manually while auto-sync running (though async/await handles this mostly, flag is safer)
            if(isSyncing) return;
            isSyncing = true;
            
            document.getElementById('loading').classList.remove('hidden');
            
            try {
                await fetch('api.php?action=sync');
                if(currentEmail) refreshMessages();
            } catch(e) {
                console.error("Sync error:", e);
            } finally {
                document.getElementById('loading').classList.add('hidden');
                isSyncing = false;
                
                // Schedule next run
                if(currentEmail) {
                    if(refreshInterval) clearTimeout(refreshInterval);
                    refreshInterval = setTimeout(sync, 20000);
                }
            }
        }


        async function refreshMessages() {
            if(!currentEmail) return;
            const res = await fetch(`api.php?action=messages&email=${encodeURIComponent(currentEmail)}`);
            const data = await res.json();
            renderInbox(data.messages);
        }

        function timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " years ago";
            
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " months ago";
            
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + " days ago";
            
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " hours ago";
            
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " mins ago";
            
            return Math.floor(seconds) + " seconds ago";
        }

        function renderInbox(msgs) {
            const tbody = document.getElementById('email-tbody');
            tbody.innerHTML = '';
            
            if(!msgs || msgs.length === 0) {
                document.getElementById('empty-inbox').style.display = 'block';
                return;
            } else {
                document.getElementById('empty-inbox').style.display = 'none';
            }

            msgs.forEach(m => {
                const tr = document.createElement('tr');
                tr.className = 'email-row' + (m.is_read == 0 ? ' unread' : '');
                tr.onclick = () => viewMessage(m.id);
                
                tr.innerHTML = `
                    <td>${m.sender}</td>
                    <td>${m.subject || '(No Subject)'}</td>
                    <td>${timeAgo(m.received_at)}</td>
                    <td><button class="danger" style="padding:0.25rem 0.5rem; font-size:0.75rem;" onclick="deleteMsg(${m.id}); event.stopPropagation();">Delete</button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function viewMessage(id) {
            const res = await fetch(`api.php?action=message&id=${id}`);
            const data = await res.json();
            const m = data.message;

            document.getElementById('inbox-view').classList.add('hidden');
            document.getElementById('detail-view').classList.remove('hidden');

            document.getElementById('msg-subject').textContent = m.subject;
            document.getElementById('msg-sender').textContent = `From: ${m.sender}`;
            document.getElementById('msg-date').textContent = m.received_at;
            
            const doc = document.getElementById('msg-frame').contentWindow.document;
            doc.open();
            doc.write(`<style>body{font-family:sans-serif;padding:1rem;}</style>` + (m.body_html || `<pre>${m.body_text}</pre>`));
            doc.close();
        }

        async function deleteMsg(id) {
            showModal('Delete Message', 'Are you sure you want to delete this message?', async () => {
                await fetch('api.php?action=delete_message', {method:'POST',body:JSON.stringify({id})});
                refreshMessages();
            }, true);
        }

        /* Accounts View Logic */
        function showAccountsView() {
            document.getElementById('workspace').style.display = 'none';
            document.getElementById('status-view').classList.add('hidden'); // Ensure Status View is hidden
            const accountsView = document.getElementById('accounts-view');
            accountsView.classList.remove('hidden');
            accountsView.style.display = 'block';
            accountPage = 1; // Reset to page 1
            loadAccounts();
        }
        
        let debounceTimer;
        function debounceLoadAccounts() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                accountPage = 1;
                loadAccounts();
            }, 300);
        }
        
        function changeAccountPage(delta) {
             const newPage = accountPage + delta;
             if(newPage < 1) return;
             accountPage = newPage;
             loadAccounts();
        }
        
        function switchToAccount(email) {
             setCurrentEmail(email);
             showDashboard(true);
        }

        async function loadAccounts() {
            const search = document.getElementById('account-search').value;
            const res = await fetch(`api.php?action=accounts&page=${accountPage}&limit=${accountLimit}&search=${encodeURIComponent(search)}`);
            const data = await res.json();
            const tbody = document.getElementById('accounts-tbody');
            tbody.innerHTML = '';
            
            // Update page info
            const total = data.total || 0;
            const totalPages = Math.ceil(total / accountLimit) || 1;
            document.getElementById('account-page-info').textContent = `Page ${accountPage} of ${totalPages} (Total: ${total})`;
            
            if(!data.accounts || data.accounts.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">No active accounts found</td></tr>';
                 return;
            }

            data.accounts.forEach(a => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight:600;color:var(--primary);">${a.recipient}</td>
                    <td>${a.count}</td>
                    <td style="font-size:0.85rem;color:var(--text-muted);">${timeAgo(a.first_seen)}</td>
                    <td style="font-size:0.85rem;color:var(--text-muted);">${timeAgo(a.last_seen)}</td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <button class="secondary" style="padding:0.25rem 0.5rem; font-size:0.75rem;" onclick="switchToAccount('${a.recipient}')">Switch</button>
                            <button class="danger" style="padding:0.25rem 0.5rem; font-size:0.75rem;" onclick="deleteAccountFromList('${a.recipient}')">Delete</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function deleteAccountFromList(email) {
            showModal('Delete Account', `Delete all data for <b>${email}</b>?`, async () => {
                await fetch('api.php?action=delete_account', {method:'POST',body:JSON.stringify({email})});
                loadAccounts();
                // If we deleted the currently active user, we should reset the workspace state too
                if(currentEmail === email) {
                    localStorage.removeItem('temp_email');
                    currentEmail = '';
                }
            }, true);
        }

        // Override/Update showDashboard to handle hiding accounts view
        const originalShowDashboard = showDashboard;
        showDashboard = function(forceWorkspace = false) {
             document.getElementById('auth-screen').style.display = 'none';
             const dash = document.getElementById('dashboard');
             dash.classList.remove('hidden');
             dash.style.display = 'flex';
             
             // Hide accounts view
             document.getElementById('accounts-view').classList.add('hidden');
             document.getElementById('status-view').classList.add('hidden');
             document.getElementById('workspace').style.display = 'contents';

             // If just switching views, don't re-init everything unless necessary
             // But existing logic is safe to run
             loadDomains();
             if(currentEmail) showActiveState();
             else showLandingState();
        }
    </script>
</body>
</html>
