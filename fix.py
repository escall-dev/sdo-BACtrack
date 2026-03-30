import re

with open('admin/landing.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern for Project Tracker card
tracker_pattern = re.compile(r'<div class="data-card">\s*<div class="card-header">\s*<i class="fas fa-search"></i> Project Tracker\s*</div>.*?</div>\s*</div>', re.DOTALL)
tracker_match = tracker_pattern.search(content)
tracker_html = tracker_match.group(0)

# Pattern for Projects List card
projects_pattern = re.compile(r'<!-- Projects List -->.*?<div class="data-card">\s*<div class="card-header">\s*<i class="fas fa-folder-open"></i> Projects List.*?</div>\s*</div>', re.DOTALL)
projects_match = projects_pattern.search(content)

# We want to insert the paginated projects list HTML
new_projects_html = """            <!-- Projects List -->
            <?php
            require_once __DIR__ . '/../models/Project.php';
            $projectModel = new Project();
            $allProjects = $projectModel->getAll([]);
            
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 5;
            $totalProjects = count($allProjects);
            $totalPages = ceil($totalProjects / $perPage);
            $projects = array_slice($allProjects, ($page - 1) * $perPage, $perPage);
            ?>
            <div class="data-card">
                <div class="card-header">
                    <i class="fas fa-folder-open"></i> Projects List
                </div>
                <div class="card-body">
                <?php if (empty($allProjects)): ?>
                    <div class="empty-state" style="text-align:center;padding:32px 0;">
                        <div class="empty-icon" style="font-size:2.5rem;color:var(--text-muted);"><i class="fas fa-folder-plus"></i></div>
                        <h3 style="margin:12px 0 6px;">No projects found</h3>
                        <p style="color:var(--text-muted);">No BAC projects have been created yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width:100%;font-size:0.85rem;border-collapse:collapse;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="text-align:center; width: 140px; border:1px solid #e2e8f0; padding:8px 6px;">Project Number</th>
                                    <th style="text-align:center; border:1px solid #e2e8f0; padding:8px 6px;">Project Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                <tr style="background:#fff;">
                                    <td style="text-align:center;font-weight:600;vertical-align:middle;border:1px solid #e2e8f0; padding:8px 6px;">
                                        <?php printf('PR-%04d', $project['id']); ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;border:1px solid #e2e8f0; padding:8px 6px;">
                                        <a href="<?php echo APP_URL; ?>/admin/project-view.php?id=<?php echo $project['id']; ?>" style="color: #0f4c75; font-weight: 600; text-decoration: none; display:inline-block;">
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>">&laquo;</a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">&raquo;</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            </div>"""

# Delete the project tracker and projects list from their old positions
content = content[:tracker_match.start()] + content[tracker_match.end():]

# Notice that indices change, so let's do search again since content modified
projects_match = re.search(r'<!-- Projects List -->.*?<div class="data-card">\s*<div class="card-header">\s*<i class="fas fa-folder-open"></i> Projects List.*?</div>\s*</div>', content, re.DOTALL)
content = content[:projects_match.start()] + content[projects_match.end():]

# Find where <!-- Detailed Procurement Timeline Planner (table) --> is located
estimator_pattern = re.compile(r'<!-- Detailed Procurement Timeline Planner \(table\) -->\s*<div class="data-card".*?</div>\s*</div>\s*</div>', re.DOTALL)
estimator_match = estimator_pattern.search(content)

# We want to change the estimator wrapper sizes, since the user wants it to be minimal
estimator_html = estimator_match.group(0)

# Build the new HTML replacement
new_html = f"""{tracker_html}

            <div class="split-container">
                {estimator_html}

{new_projects_html}
            </div>"""

content = content[:estimator_match.start()] + new_html + content[estimator_match.end():]

with open('admin/landing.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Done")
