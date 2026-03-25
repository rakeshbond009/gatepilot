<?php
/**
 * Manage Register Types
 * Only accessible by those with pages.register_types permission
 */

if (!hasPermission('pages.register_types') && (!isset($_SESSION['super_admin']) || $_SESSION['super_admin'] != 1)) {
    die("Unauthorized access. You do not have permission to manage register types.");
}

$registers_manager = new DynamicRegisters($conn);
$message = '';
$msg_type = 'success';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_type'])) {
        $slug = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['slug']));
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon'] ?: '📝');
        $color = mysqli_real_escape_string($conn, $_POST['color'] ?: '#4f46e5');

        $raw_fields = $_POST['fields_json'] ?: '[]';
        $test = json_decode($raw_fields);

        if (json_last_error() === JSON_ERROR_NONE) {
            $fields_json_stored = mysqli_real_escape_string($conn, $raw_fields);

            // Check if slug or title already exists
            $check = mysqli_query($conn, "SELECT id FROM register_types WHERE slug = '$slug' OR title = '$title'");
            if (mysqli_num_rows($check) > 0) {
                $message = "❌ Error: A register with this Display Title or Unique ID already exists.";
                $msg_type = 'error';
            }
            else {
                $sql = "INSERT INTO register_types (slug, title, icon, color, fields_json, is_active) VALUES ('$slug', '$title', '$icon', '$color', '$fields_json_stored', 1)";
                if (mysqli_query($conn, $sql)) {
                    $message = "✅ Success: New register type '$title' created.";
                    $msg_type = 'success';
                }
                else {
                    $message = "❌ Error: " . mysqli_error($conn);
                    $msg_type = 'error';
                }
            }
        }
        else {
            $message = "❌ Error: Invalid fields structure. Please use the builder.";
            $msg_type = 'error';
        }
    }

    if (isset($_POST['update_fields'])) {
        $id = intval($_POST['type_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
        $icon = mysqli_real_escape_string($conn, $_POST['icon'] ?? '');
        $color = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $fields_json_raw = $_POST['fields_json'] ?? '[]';
        $fields_json_esc = mysqli_real_escape_string($conn, $fields_json_raw);

        $test = json_decode($fields_json_raw);
        if (json_last_error() === JSON_ERROR_NONE) {
            $sql = "UPDATE register_types SET 
                    title = '$title',
                    icon = '$icon',
                    color = '$color',
                    is_active = $is_active,
                    fields_json = '$fields_json_esc' 
                    WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Success: Register type '$title' updated.";
                $msg_type = 'success';
            }
            else {
                $message = "❌ Error: " . mysqli_error($conn);
                $msg_type = 'error';
            }
        }
        else {
            $message = "❌ Error: Invalid JSON structure. Update aborted.";
            $msg_type = 'error';
        }
    }

    if (isset($_POST['delete_type']) && $_POST['delete_type'] == '1') {
        $id = intval($_POST['type_id']);
        // Check if entries exist
        $slug_res = mysqli_query($conn, "SELECT slug FROM register_types WHERE id = $id");
        $slug_row = mysqli_fetch_assoc($slug_res);
        $slug = $slug_row['slug'];

        $check_entries = mysqli_query($conn, "SELECT id FROM manual_registers WHERE register_type = '$slug' LIMIT 1");
        if (mysqli_num_rows($check_entries) > 0) {
            $message = "❌ Error: Cannot delete. This register already has entries logged.";
            $msg_type = 'error';
        }
        else {
            if (mysqli_query($conn, "DELETE FROM register_types WHERE id = $id")) {
                $message = "✅ Success: Register type deleted.";
                $msg_type = 'success';
            }
        }
    }
}

// Fetch all types including inactive ones
$types = $registers_manager->getAllTypes(false);
?>

<div class="container" style="padding-top: 20px;">
    <!-- Standard Back Button -->
    <a href="?page=admin" class="btn btn-secondary btn-full" style="margin-bottom: 20px; display: block;">← Back to Admin</a>
    
    <!-- Hero Header Card -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 40px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));">⚙️</div>
            <div>
                <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Register Configuration</h1>
                <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Define fields and custom logic for manual registers</p>
            </div>
        </div>
    </div>

        <?php if ($message): ?>
            <div class="alert-banner <?php echo $msg_type; ?>" style="margin-bottom: 30px; animation: slideDown 0.4s ease-out;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 24px;"><?php echo($msg_type == 'success' ? '✅' : '❌'); ?></span>
                    <span style="font-weight: 700; color: <?php echo($msg_type == 'success' ? '#166534' : '#991b1b'); ?>;"><?php echo $message; ?></span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size: 20px;">×</button>
            </div>
        <?php
endif; ?>

        <div style="display: grid; grid-template-columns: 350px 1fr; gap: 40px; align-items: start;">
            
            <!-- Left Panel: Create Form -->
            <div class="config-side-panel">
                <div class="premium-card">
                    <div class="card-header" style="background: #4f46e5; color: white;">
                        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: rgba(255,255,255,0.2); padding: 5px; border-radius: 8px;">➕</span>
                            Create New Register
                        </h3>
                    </div>
                    <form method="POST" style="padding: 25px;">
                        <input type="hidden" name="add_type" value="1">
                        
                        <?php $unique_suffix = rand(10000, 99999); ?>
                        <div class="form-group-p">
                            <label>Display Title</label>
                            <input type="text" name="title" required placeholder="e.g. Visitor Outward" onkeyup="document.getElementById('slug_input').value = this.value ? this.value.toLowerCase().replace(/[^a-z0-9]/g, '_') + '_<?php echo $unique_suffix; ?>' : ''">
                        </div>

                        <div class="form-group-p">
                            <label>Internal Slug (Unique ID)</label>
                            <input type="text" name="slug" id="slug_input" required readonly placeholder="e.g. visitor_outward_<?php echo $unique_suffix; ?>" pattern="[a-z0-9_]+">
                            <small>Generated automatically to remain unique</small>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group-p">
                                <label>Icon (Emoji) <a href="https://emojipedia.org/" target="_blank" style="float:right;">🔗</a></label>
                                <input type="text" name="icon" placeholder="📝" maxlength="4">
                            </div>
                            <div class="form-group-p">
                                <label>Theme Color</label>
                                <input type="color" name="color" value="#4f46e5" style="height: 48px; cursor: pointer;">
                            </div>
                        </div>

                        <!-- Instructions for Emoji -->
                        <div class="emoji-info">
                            <strong>How to use Emojis:</strong> Click the link above, copy any symbol like 🚚, 🏢, 📦 and paste it in the Icon field.
                        </div>

                        <div class="divider"></div>

                        <div class="builder-section">
                            <label style="font-weight: 800; color: #1e293b; margin-bottom: 15px; display: block;">🛠️ Field Definitions</label>
                            
                            <div id="new_type_builder" class="field-builder" data-json-id="new_type_json">
                                <!-- Rows populate from Default JSON -->
                            </div>

                            <button type="button" class="btn-outline" onclick="addFieldToBuilder('new_type_builder', 'new_type_json')">
                                ➕ Add Custom Field
                            </button>
                        </div>

                        <!-- Hidden JSON but preserved for sync -->
                        <textarea name="fields_json" id="new_type_json" style="display:none;"><?php
$default_fields = [
    ['name' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local', 'required' => true],
    ['name' => 'vehicle_no', 'label' => 'Vehicle No', 'type' => 'text', 'required' => true]
];
echo htmlspecialchars(json_encode($default_fields));
?></textarea>

                        <button type="submit" class="btn-primary-p">🚀 Create Register Type</button>
                    </form>
                </div>
            </div>

            <!-- Right Panel: List and Edit -->
            <div class="config-right-panel">
                <!-- Configuration Search -->
                <div class="premium-card config-search-card" style="margin-bottom: 30px; padding: 25px; overflow: visible !important;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; font-size: 18px; color: #334155;">🔍 Find Register to Configure</h3>
                        <button type="button" onclick="resetConfigSearch()" class="btn btn-sm" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;">Reset Search</button>
                    </div>
                    
                    <div class="custom-select-container" style="position: relative;">
                        <input type="text" id="config_search_display" placeholder="Type to search registers..." readonly
                            onclick="toggleConfigDropdown()"
                            style="font-size: 16px; padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; width: 100%; background-color: #f9fafb; cursor: pointer; box-sizing: border-box;">
                        
                        <div id="config_dropdown_options" class="dropdown-options"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); z-index: 2000; max-height: 400px; overflow-y: auto; margin-top: 5px;">
                            <input type="text" id="config_filter_box" placeholder="Type name or slug..."
                                onkeyup="filterConfigOptions()" onclick="event.stopPropagation()"
                                style="width: 100%; padding: 12px; border: none; border-bottom: 1px solid #e5e7eb; outline: none; background: #f8fafc; font-size: 14px; position: sticky; top: 0; box-sizing: border-box;">
                            <div id="config_list_container">
                                <?php foreach ($types as $type): ?>
                                    <div class="config-opt-item" 
                                         data-id="register_<?php echo $type['id']; ?>" 
                                         data-title="<?php echo htmlspecialchars($type['title']); ?>"
                                         data-search="<?php echo strtolower($type['title'] . ' ' . $type['slug']); ?>"
                                         onclick="selectConfig('<?php echo $type['id']; ?>', '<?php echo htmlspecialchars($type['title']); ?>')"
                                         style="padding: 12px 15px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; transition: background 0.2s;">
                                        <span style="font-size: 20px;"><?php echo $type['icon']; ?></span>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 600; color: #334155;"><?php echo htmlspecialchars($type['title']); ?></span>
                                            <code style="font-size: 11px; color: #64748b;">/<?php echo $type['slug']; ?></code>
                                        </div>
                                    </div>
                                <?php
endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="no_selection_msg" style="background: white; padding: 80px; text-align: center; border-radius: 24px; border: 2px dashed #cbd5e1; margin-bottom: 30px;">
                    <span style="font-size: 50px;">🔍</span>
                    <h3 style="color: #64748b; margin-top: 20px;">Use the search above to find and edit a register.</h3>
                </div>

                <?php if (empty($types)): ?>
                    <div style="background: white; padding: 100px; text-align: center; border-radius: 24px; border: 2px dashed #cbd5e1;">
                        <span style="font-size: 60px;">📭</span>
                        <h3 style="color: #64748b; margin-top: 20px;">No registers defined yet.</h3>
                    </div>
                <?php
endif; ?>

                <div style="display: flex; flex-direction: column; gap: 25px;">
                    <?php foreach ($types as $type): ?>
                        <div id="register_<?php echo $type['id']; ?>" class="premium-card register-item <?php echo $type['is_active'] ? 'active' : 'inactive'; ?>" style="border-left: 8px solid <?php echo $type['color']; ?>; display: none; margin-bottom: 30px;">
                            <form method="POST">
                                <input type="hidden" name="update_fields" value="1">
                                <input type="hidden" name="type_id" value="<?php echo $type['id']; ?>">

                                <div class="item-header">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="item-icon" style="background: <?php echo $type['color']; ?>20; color: <?php echo $type['color']; ?>;">
                                            <?php echo $type['icon']; ?>
                                        </div>
                                        <div>
                                            <h3 style="margin: 0;"><?php echo htmlspecialchars($type['title']); ?></h3>
                                            <code>/<?php echo $type['slug']; ?></code>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="status-toggle">
                                            <input type="checkbox" name="is_active" id="active_<?php echo $type['id']; ?>" <?php echo $type['is_active'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                            <label for="active_<?php echo $type['id']; ?>">Active</label>
                                        </div>
                                        <button type="button" class="btn-toggle-json" onclick="toggleJSON('json_container_<?php echo $type['id']; ?>')">Developer Mode</button>
                                        <button type="button" class="btn-delete" onclick="if(confirm('Delete this register? This action is irreversible if no entries exist.')) { this.form.delete_type.value=1; this.form.submit(); }">🗑️</button>
                                        <input type="hidden" name="delete_type" value="0">
                                    </div>
                                </div>

                                <div class="item-body">
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;">
                                        <div class="form-group-p">
                                            <label>Public Title</label>
                                            <input type="text" name="title" value="<?php echo htmlspecialchars($type['title']); ?>" required>
                                        </div>
                                        <div class="form-group-p">
                                            <label>Icon</label>
                                            <input type="text" name="icon" value="<?php echo htmlspecialchars($type['icon']); ?>">
                                        </div>
                                        <div class="form-group-p">
                                            <label>Border Color</label>
                                            <input type="color" name="color" value="<?php echo $type['color']; ?>" style="height: 48px; padding: 2px;">
                                        </div>
                                    </div>

                                    <div class="visual-builder-container">
                                        <label>Modify Fields</label>
                                        <div id="builder_<?php echo $type['id']; ?>" class="field-builder" data-json-id="json_<?php echo $type['id']; ?>">
                                            <!-- Hydrated by JS -->
                                        </div>
                                        <button type="button" class="btn-add-row" onclick="addFieldToBuilder('builder_<?php echo $type['id']; ?>', 'json_<?php echo $type['id']; ?>')">
                                            + Add Field
                                        </button>
                                    </div>

                                    <div id="json_container_<?php echo $type['id']; ?>" class="json-advanced" style="display: none;">
                                        <label>Advanced JSON Configuration</label>
                                        <textarea name="fields_json" id="json_<?php echo $type['id']; ?>" oninput="syncJSONToBuilder('builder_<?php echo $type['id']; ?>', 'json_<?php echo $type['id']; ?>')"><?php echo htmlspecialchars(json_encode($type['fields'], JSON_PRETTY_PRINT)); ?></textarea>
                                    </div>
                                </div>

                                <div class="item-footer">
                                    <button type="submit" class="btn-save">💾 Save Changes</button>
                                </div>
                            </form>
                        </div>
                    <?php
endforeach; ?>
                </div>
            </div><!-- end config-right-panel -->
</div><!-- end container -->
</div><!-- end register-manager-wrapper (closed by container) -->

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');

    .btn-glass {
        padding: 10px 20px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        backdrop-filter: blur(5px);
        transition: all 0.3s;
    }
    .btn-glass:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }

    .premium-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: visible; /* Changed from hidden to allow dropdowns */
        border: 1px solid #e2e8f0;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .register-item.inactive { opacity: 0.7; filter: grayscale(0.5); border-left: 8px solid #94a3b8 !important; }
    
    .card-header { padding: 15px 25px; }
    
    .form-group-p { margin-bottom: 20px; }
    .form-group-p label { display: block; font-weight: 700; font-size: 13px; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group-p input { width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 15px; outline: none; transition: all 0.3s; box-sizing: border-box; }
    .form-group-p input:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
    
    .emoji-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px; border-radius: 12px; font-size: 12px; line-height: 1.4; margin-bottom: 20px; }

    .divider { height: 1px; background: #e2e8f0; margin: 25px 0; }
    
    .builder-section { background: #f8fafc; padding: 20px; border-radius: 16px; border: 1.5px dashed #cbd5e1; }
    
    .btn-primary-p { width: 100%; padding: 14px; background: #4f46e5; color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s; margin-top: 10px; }
    .btn-primary-p:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4); }

    .btn-outline { width: 100%; padding: 10px; background: white; border: 1.5px solid #4f46e5; color: #4f46e5; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; margin: 10px 0; }
    .btn-outline:hover { background: #f5f3ff; }

    .item-header { padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
    .item-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    
    .item-body { padding: 25px; }
    .item-footer { padding: 15px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; }
    
    .btn-save { padding: 10px 25px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
    .btn-save:hover { background: #059669; transform: scale(1.05); }

    .btn-delete { background: #fee2e2; border: none; color: #ef4444; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .btn-delete:hover { background: #ef4444; color: white; }

    .btn-add-row { background: none; border: 2px dashed #cbd5e1; color: #64748b; padding: 10px; width: 100%; border-radius: 10px; cursor: pointer; font-weight: 600; margin-top: 10px; }
    .btn-add-row:hover { border-color: #4f46e5; color: #4f46e5; background: #f5f3ff; }

    .builder-row { display: grid; grid-template-columns: 2fr 1fr 60px 40px; gap: 10px; margin-bottom: 10px; align-items: center; padding: 12px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; }
    .builder-row input, .builder-row select { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; }
    .builder-row input:focus { border-color: #4f46e5; }

    .json-advanced textarea { width: 100%; height: 200px; font-family: 'Fira Code', monospace; font-size: 12px; padding: 15px; border-radius: 12px; background: #1e293b; color: #e2e8f0; border: none; margin-top: 10px; line-height: 1.6; }

    .alert-banner { padding: 15px 25px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; }
    .alert-banner.success { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .alert-banner.error { background: #fef2f2; border: 1px solid #fecaca; }

    .btn-toggle-json { background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b; padding: 8px 15px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .btn-toggle-json:hover { background: #e2e8f0; }

    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .visual-builder-container { margin-top: 25px; }
    .visual-builder-container label { font-weight: 700; color: #334155; display: block; margin-bottom: 10px; }
</style>

<script>
// ==================== CONFIG SEARCH LOGIC ====================
function toggleConfigDropdown() {
    const dd = document.getElementById('config_dropdown_options');
    const isHidden = dd.style.display === 'none';
    dd.style.display = isHidden ? 'block' : 'none';
    if (isHidden) {
        document.getElementById('config_filter_box').value = '';
        filterConfigOptions();
        document.getElementById('config_filter_box').focus();
    }
}

function filterConfigOptions() {
    const filter = document.getElementById('config_filter_box').value.toLowerCase();
    const items = document.querySelectorAll('.config-opt-item');
    items.forEach(item => {
        const text = item.getAttribute('data-search');
        item.style.display = text.includes(filter) ? 'flex' : 'none';
    });
}

function selectConfig(id, title) {
    // Hide all items
    document.querySelectorAll('.register-item').forEach(el => el.style.display = 'none');
    document.getElementById('no_selection_msg').style.display = 'none';
    
    // Show selected
    const selected = document.getElementById('register_' + id);
    if (selected) {
        selected.style.display = 'block';
        selected.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    document.getElementById('config_search_display').value = title;
    document.getElementById('config_dropdown_options').style.display = 'none';
}

function resetConfigSearch() {
    document.getElementById('config_search_display').value = '';
    document.querySelectorAll('.register-item').forEach(el => el.style.display = 'none');
    document.getElementById('no_selection_msg').style.display = 'block';
}

// Close on outside click
document.addEventListener('click', function(e) {
    const searchCard = document.querySelector('.config-search-card');
    if (searchCard && !searchCard.contains(e.target)) {
        const dd = document.getElementById('config_dropdown_options');
        if (dd) dd.style.display = 'none';
    }
});

// Sync data-search on load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.config-opt-item').forEach(item => {
        item.onmouseover = () => item.style.background = '#f8fafc';
        item.onmouseout = () => item.style.background = 'white';
    });
});

// ==================== FIELD BUILDER LOGIC ====================
function addFieldToBuilder(containerId, jsonId, data = null) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const row = document.createElement('div');
    row.className = 'builder-row';
    
    const label = data ? data.label : '';
    const type = data ? data.type : 'datetime-local';
    const isRequired = (data && data.required === false) ? false : true;

    row.innerHTML = `
        <input type="text" value="${label}" placeholder="Field Label" class="b-label" oninput="syncBuilderToJSON('${containerId}', '${jsonId}')">
        <select class="b-type" onchange="syncBuilderToJSON('${containerId}', '${jsonId}')">
            <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
            <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
            <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
            <option value="time" ${type === 'time' ? 'selected' : ''}>Time</option>
            <option value="datetime-local" ${type === 'datetime-local' ? 'selected' : ''}>Datetime</option>
            <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
        </select>
        <div style="display:flex; align-items:center; justify-content:center; gap:5px; font-size:11px; font-weight:bold;">
            <input type="checkbox" class="b-req" ${isRequired ? 'checked' : ''} onchange="syncBuilderToJSON('${containerId}', '${jsonId}')"> REQ
        </div>
        <button type="button" onclick="this.parentElement.remove(); syncBuilderToJSON('${containerId}', '${jsonId}')" style="background:none; border:none; cursor:pointer; font-size: 16px;">🗑️</button>
    `;
    container.appendChild(row);
    if (!data) syncBuilderToJSON(containerId, jsonId);
}

function syncBuilderToJSON(containerId, jsonId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.builder-row');
    const fields = [];
    
    rows.forEach(row => {
        const labelInput = row.querySelector('.b-label');
        if (!labelInput) return;
        const label = labelInput.value.trim();
        const type = row.querySelector('.b-type').value;
        const required = row.querySelector('.b-req').checked;
        if (label) {
            const name = label.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/^_+|_+$/g, '');
            fields.push({
                name: name,
                label: label,
                type: type,
                required: required
            });
        }
    });
    
    document.getElementById(jsonId).value = JSON.stringify(fields, null, 2);
}

function syncJSONToBuilder(containerId, jsonId) {
    const jsonInput = document.getElementById(jsonId);
    if (!jsonInput) return;
    const jsonStr = jsonInput.value;
    const container = document.getElementById(containerId);
    if (!container) return;
    try {
        const fields = JSON.parse(jsonStr);
        if (Array.isArray(fields)) {
            container.innerHTML = '';
            fields.forEach(f => addFieldToBuilder(containerId, jsonId, f));
        }
    } catch (e) {
        console.error("JSON Parse Error: ", e);
    }
}

function toggleJSON(containerId) {
    const el = document.getElementById(containerId);
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.field-builder').forEach(b => {
        const jsonId = b.getAttribute('data-json-id');
        syncJSONToBuilder(b.id, jsonId);
    });
});
</script>
