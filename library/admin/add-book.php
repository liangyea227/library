<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
} else {

if(isset($_POST['add'])){
  $bookname    = $_POST['bookname'];
  $category    = $_POST['category'];
  $author      = $_POST['author'];
  $isbn        = $_POST['isbn'];
  $bookimg     = $_FILES["bookpic"]["name"];
  $bqty        = $_POST['bqty'];
  $description = trim($_POST['description']);
  $extension = strtolower(substr($bookimg, strrpos($bookimg, '.')));
  $allowed_extensions = array(".jpg", ".jpeg", ".png", ".gif");
  $imgnewname = md5($bookimg . time()) . $extension;

  if(empty($description)){
    echo "<script>alert('Book description cannot be blank.');</script>";
  } else if(!in_array($extension, $allowed_extensions)){
    echo "<script>alert('Invalid format. Only jpg / jpeg / png / gif allowed');</script>";
  } else {
    move_uploaded_file($_FILES["bookpic"]["tmp_name"], "bookimg/" . $imgnewname);
    $sql = "INSERT INTO tblbooks(BookName,CatId,AuthorId,ISBNNumber,bookImage,bookQty,BookDetails) VALUES(:bookname,:category,:author,:isbn,:imgnewname,:bqty,:description)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookname',    $bookname,    PDO::PARAM_STR);
    $query->bindParam(':category',    $category,    PDO::PARAM_STR);
    $query->bindParam(':author',      $author,      PDO::PARAM_STR);
    $query->bindParam(':isbn',        $isbn,        PDO::PARAM_STR);
    $query->bindParam(':imgnewname',  $imgnewname,  PDO::PARAM_STR);
    $query->bindParam(':bqty',        $bqty,        PDO::PARAM_STR);
    $query->bindParam(':description', $description, PDO::PARAM_STR);
    $query->execute();
    $lastInsertId = $dbh->lastInsertId();
    if($lastInsertId){
      echo "<script>alert('Book listed successfully'); window.location.href='manage-books.php';</script>";
    } else {
      echo "<script>alert('Something went wrong. Please try again'); window.location.href='manage-books.php';</script>";
    }
  }
}

/* Fetch all authors for the searchable dropdown */
$sqla = "SELECT id, AuthorName FROM tblauthors ORDER BY AuthorName ASC";
$qa   = $dbh->prepare($sqla);
$qa->execute();
$allAuthors = $qa->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Add Book</title>
  <link href="assets/css/style.css" rel="stylesheet"/>

  <style>
    /* ── Searchable Author Dropdown ── */
    .author-picker          { position: relative; }

    .author-search-wrap     {
      position: relative;
      display: flex;
      align-items: center;
    }

    .author-search-input    {
      width: 100%;
      background: var(--cream);
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      padding: 11px 40px 11px 14px;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      color: var(--text);
      transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
      cursor: text;
    }
    .author-search-input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(201,149,76,0.12);
      background: var(--white);
      outline: none;
    }
    .author-search-input.has-selection {
      border-color: var(--sage);
      background: var(--white);
    }

    /* clear (×) button — shown only when an author is selected */
    .author-clear {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 16px;
      cursor: pointer;
      display: none;
      line-height: 1;
      padding: 0;
    }
    .author-clear:hover { color: var(--rust); }

    /* dropdown list */
    .author-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      background: var(--white);
      border: 1.5px solid var(--gold);
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
      max-height: 220px;
      overflow-y: auto;
      z-index: 999;
    }
    .author-dropdown.open { display: block; }

    .author-option {
      padding: 10px 14px;
      font-size: 14px;
      color: var(--text);
      cursor: pointer;
      transition: background var(--transition);
    }
    .author-option:hover,
    .author-option.highlighted {
      background: var(--cream-dark);
      color: var(--ink);
    }
    .author-option.selected {
      background: rgba(201,149,76,0.12);
      font-weight: 600;
      color: var(--gold);
    }

    .author-no-results {
      padding: 10px 14px;
      font-size: 13px;
      color: var(--text-muted);
      font-style: italic;
    }

    /* selected badge shown below the input */
    .author-selected-badge {
      display: none;
      align-items: center;
      gap: 8px;
      margin-top: 6px;
      background: rgba(74,124,111,0.10);
      border: 1px solid var(--sage-light);
      border-radius: 20px;
      padding: 5px 12px;
      font-size: 13px;
      color: var(--sage);
      font-weight: 500;
      width: fit-content;
    }
    .author-selected-badge span { flex: 1; }
  </style>

  <script>
  function checkisbnAvailability(){
    jQuery.ajax({
      url:"check_availability.php",
      data:'isbn='+jQuery("#isbn").val(),
      type:"POST",
      success:function(data){ jQuery("#isbn-availability-status").html(data); },
      error:function(){}
    });
  }
  </script>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Add Book</h1>
      <p class="page-subtitle">Add a new book to the library collection</p>
    </div>
    <a href="manage-books.php" class="btn btn-primary">← Back to Books</a>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Book Information</span>
    </div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data" id="addBookForm">
        <div class="form-grid">

          <div class="form-group">
            <label>Book Name <span class="req">*</span></label>
            <input type="text" name="bookname" autocomplete="off" required/>
          </div>

          <div class="form-group">
            <label>Category <span class="req">*</span></label>
            <select name="category" required>
              <option value="">Select Category</option>
              <?php
                $status = 1;
                $sqlc = "SELECT * FROM tblcategory WHERE Status=:status";
                $qc = $dbh->prepare($sqlc); $qc->bindParam(':status',$status,PDO::PARAM_STR); $qc->execute();
                foreach($qc->fetchAll(PDO::FETCH_OBJ) as $r){
                  echo '<option value="'.htmlentities($r->id).'">'.htmlentities($r->CategoryName).'</option>';
                }
              ?>
            </select>
          </div>

          <!-- ── SEARCHABLE AUTHOR PICKER ── -->
          <div class="form-group">
            <label>Author <span class="req">*</span></label>

            <!-- Hidden input that carries the real author id on submit -->
            <input type="hidden" name="author" id="authorId" required/>

            <div class="author-picker">
              <div class="author-search-wrap">
                <input
                  type="text"
                  id="authorSearch"
                  class="author-search-input"
                  placeholder="Type to search author…"
                  autocomplete="off"
                  aria-label="Search author"
                />
                <button type="button" class="author-clear" id="authorClear" title="Clear selection">&#x2715;</button>
              </div>

              <!-- Dropdown list — populated from PHP author array via JS -->
              <div class="author-dropdown" id="authorDropdown" role="listbox"></div>

              <!-- Badge shown after a selection is made -->
              <div class="author-selected-badge" id="authorBadge">
                <span id="authorBadgeName"></span>
              </div>
            </div>
            <span class="help-text">Start typing the author's name to filter the list</span>
          </div>
          <!-- ── END AUTHOR PICKER ── -->

          <div class="form-group">
            <label>ISBN Number <span class="req">*</span></label>
            <input type="text" name="isbn" id="isbn" required autocomplete="off" onblur="checkisbnAvailability()"/>
            <span class="help-text">Must be unique — checked on blur</span>
            <span id="isbn-availability-status"></span>
          </div>

          <div class="form-group">
            <label>Book Quantity <span class="req">*</span></label>
            <input type="text" name="bqty" autocomplete="off" required/>
          </div>

          <div class="form-group full">
            <label>Book Description <span class="req">*</span></label>
            <textarea name="description" rows="4" autocomplete="off" required placeholder="Enter a brief description of the book..." style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;resize:vertical;"></textarea>
          </div>

          <div class="form-group full">
            <label>Book Cover Image <span class="req">*</span></label>
            <input type="file" name="bookpic" required/>
            <span class="help-text">Accepted formats: jpg, jpeg, png, gif</span>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" name="add" class="btn btn-gold">Add Book</button>
          <a href="manage-books.php" class="btn btn-primary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>

<script>
(function () {
  /* ── Author data from PHP ── */
  var AUTHORS = <?php
    $out = [];
    foreach($allAuthors as $a){
      $out[] = ['id' => (int)$a->id, 'name' => htmlspecialchars($a->AuthorName, ENT_QUOTES)];
    }
    echo json_encode($out);
  ?>;

  /* ── DOM refs ── */
  var searchInput  = document.getElementById('authorSearch');
  var hiddenInput  = document.getElementById('authorId');
  var dropdown     = document.getElementById('authorDropdown');
  var clearBtn     = document.getElementById('authorClear');
  var badge        = document.getElementById('authorBadge');
  var badgeName    = document.getElementById('authorBadgeName');

  var highlightIdx = -1;   // keyboard navigation index

  /* ── Render dropdown items matching the query ── */
  function renderDropdown(query) {
    dropdown.innerHTML = '';
    highlightIdx = -1;

    var q = query.trim().toLowerCase();
    var matches = AUTHORS.filter(function(a) {
      return q === '' || a.name.toLowerCase().indexOf(q) !== -1;
    });

    if (matches.length === 0) {
      dropdown.innerHTML = '<div class="author-no-results">No authors found for "' + escHtml(query) + '"</div>';
    } else {
      matches.forEach(function(a, i) {
        var div = document.createElement('div');
        div.className = 'author-option' + (a.id === parseInt(hiddenInput.value) ? ' selected' : '');
        div.setAttribute('role', 'option');
        div.setAttribute('data-id', a.id);
        div.setAttribute('data-name', a.name);
        div.setAttribute('data-idx', i);

        /* Bold-highlight the matching part */
        if (q) {
          var idx = a.name.toLowerCase().indexOf(q);
          div.innerHTML =
            escHtml(a.name.slice(0, idx)) +
            '<strong>' + escHtml(a.name.slice(idx, idx + q.length)) + '</strong>' +
            escHtml(a.name.slice(idx + q.length));
        } else {
          div.textContent = a.name;
        }

        div.addEventListener('mousedown', function(e) {
          e.preventDefault(); // keep input focused
          selectAuthor(a.id, a.name);
        });

        dropdown.appendChild(div);
      });
    }

    openDropdown();
  }

  /* ── Select an author ── */
  function selectAuthor(id, name) {
    hiddenInput.value       = id;
    searchInput.value       = name;
    searchInput.classList.add('has-selection');

    badgeName.textContent   = '✓ ' + name;
    badge.style.display     = 'flex';
    clearBtn.style.display  = 'block';

    closeDropdown();
  }

  /* ── Clear selection ── */
  function clearSelection() {
    hiddenInput.value  = '';
    searchInput.value  = '';
    searchInput.classList.remove('has-selection');
    badge.style.display    = 'none';
    clearBtn.style.display = 'none';
    searchInput.focus();
  }

  /* ── Open / close ── */
  function openDropdown() {
    dropdown.classList.add('open');
  }
  function closeDropdown() {
    dropdown.classList.remove('open');
    highlightIdx = -1;
  }

  /* ── Keyboard navigation ── */
  function moveHighlight(dir) {
    var items = dropdown.querySelectorAll('.author-option');
    if (!items.length) return;

    if (highlightIdx >= 0) items[highlightIdx].classList.remove('highlighted');
    highlightIdx = (highlightIdx + dir + items.length) % items.length;
    items[highlightIdx].classList.add('highlighted');
    items[highlightIdx].scrollIntoView({ block: 'nearest' });
  }

  /* ── Events ── */
  searchInput.addEventListener('input', function() {
    if (hiddenInput.value) clearSelection();   // typing after selection resets it
    renderDropdown(this.value);
  });

  searchInput.addEventListener('focus', function() {
    renderDropdown(this.value);
  });

  searchInput.addEventListener('keydown', function(e) {
    if (!dropdown.classList.contains('open')) return;

    if (e.key === 'ArrowDown')  { e.preventDefault(); moveHighlight(1);  return; }
    if (e.key === 'ArrowUp')    { e.preventDefault(); moveHighlight(-1); return; }
    if (e.key === 'Escape')     { closeDropdown();                        return; }

    if (e.key === 'Enter') {
      e.preventDefault();
      var items = dropdown.querySelectorAll('.author-option');
      if (highlightIdx >= 0 && items[highlightIdx]) {
        var el = items[highlightIdx];
        selectAuthor(parseInt(el.getAttribute('data-id')), el.getAttribute('data-name'));
      }
    }
  });

  clearBtn.addEventListener('click', clearSelection);

  /* Close dropdown when clicking outside */
  document.addEventListener('mousedown', function(e) {
    if (!e.target.closest('.author-picker')) closeDropdown();
  });

  /* Validate that an author was actually selected before submitting */
  document.getElementById('addBookForm').addEventListener('submit', function(e) {
    if (!hiddenInput.value) {
      e.preventDefault();
      searchInput.focus();
      searchInput.style.borderColor = 'var(--rust)';
      searchInput.setAttribute('placeholder', 'Please select an author first!');
      setTimeout(function() {
        searchInput.style.borderColor = '';
        searchInput.setAttribute('placeholder', 'Type to search author…');
      }, 2500);
    }
  });

  /* ── Utility ── */
  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>

</body>
</html>
<?php } ?>