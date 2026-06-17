<?php
session_start();
error_reporting(0);
include('includes/config.php');
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if(strlen($_SESSION['alogin'])==0){
  header('location:index.php');
  exit;
}

if(isset($_POST['issue'])){
  $studentid = strtoupper(trim($_POST['studentid']));
  $bookid    = (int)$_POST['bookid'];
  $aremark   = trim($_POST['aremark']);
  $aqty      = (int)$_POST['aqty'];

  // ── LIMIT CHECK: max 3 books per student (unreturned) ────────
  $limitSql = "SELECT COUNT(*) FROM tblissuedbookdetails
               WHERE StudentID = :studentid
               AND (RetrunStatus = 0 OR RetrunStatus IS NULL OR RetrunStatus = '')";
  $limitQ = $dbh->prepare($limitSql);
  $limitQ->bindParam(':studentid', $studentid, PDO::PARAM_STR);
  $limitQ->execute();
  $activeBorrows = (int)$limitQ->fetchColumn();

  if($activeBorrows >= 3){
    $_SESSION['error'] = "Cannot issue book: this student already has 3 books borrowed. They must return a book before borrowing another.";
    header('location:manage-issued-books.php');
    exit;
  }
  // ─────────────────────────────────────────────────────────────

  if($aqty > 0){
    $sql   = "INSERT INTO tblissuedbookdetails(StudentID,BookId,remark) VALUES(:studentid,:bookid,:aremark)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
    $query->bindParam(':bookid',    $bookid,    PDO::PARAM_INT);
    $query->bindParam(':aremark',   $aremark,   PDO::PARAM_STR);
    $query->execute();
    if($dbh->lastInsertId()){
      // FIX: update isIssued flag on the book
      $sql2 = "UPDATE tblbooks SET isIssued=1 WHERE id=:bookid";
      $q2 = $dbh->prepare($sql2);
      $q2->bindParam(':bookid', $bookid, PDO::PARAM_INT);
      $q2->execute();

      // ── Send borrow confirmation email to student ─────────────
      try {
        $stSql = $dbh->prepare("SELECT FullName, EmailId FROM tblstudents WHERE StudentId = :sid LIMIT 1");
        $stSql->bindParam(':sid', $studentid, PDO::PARAM_STR);
        $stSql->execute();
        $stRow = $stSql->fetch(PDO::FETCH_OBJ);

        $bkSql = $dbh->prepare("SELECT BookName, ISBNNumber FROM tblbooks WHERE id = :bid LIMIT 1");
        $bkSql->bindParam(':bid', $bookid, PDO::PARAM_INT);
        $bkSql->execute();
        $bkRow = $bkSql->fetch(PDO::FETCH_OBJ);

        if($stRow && !empty($stRow->EmailId)) {
          $mail = new PHPMailer(true);
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = MAIL_USER;
          $mail->Password   = MAIL_PASS;
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port       = 587;

          $mail->setFrom($mail->Username, 'Lim Library');
          $mail->addAddress($stRow->EmailId, $stRow->FullName);
          $mail->isHTML(true);
          $mail->CharSet = 'UTF-8';
          $mail->Subject = 'Book Borrowed – ' . $bkRow->BookName;
          $mail->Body = '
          <div style="font-family:DM Sans,Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px 24px;background:#f5f5fb;border-radius:16px;">
            <div style="text-align:center;margin-bottom:24px;">
              <div style="display:inline-block;background:#0000ff;color:#fff;border-radius:12px;padding:12px 20px;font-size:20px;font-weight:700;letter-spacing:1px;">LIM LIBRARY</div>
            </div>
            <div style="background:#fff;border-radius:12px;padding:28px 24px;border:1px solid #e2e2ee;">
              <p style="font-size:15px;color:#1a1a2e;font-weight:600;margin:0 0 8px;">Hi ' . htmlspecialchars($stRow->FullName) . ',</p>
              <p style="font-size:13px;color:#6b6b80;margin:0 0 20px;">A book has been issued to you at the library counter. Please return it within <strong>7 days</strong>.</p>
              <div style="background:#e8e8ff;border-radius:10px;padding:18px 20px;margin-bottom:20px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#0000ff;margin-bottom:6px;">Book Borrowed</div>
                <div style="font-size:17px;font-weight:700;color:#1a1a2e;margin-bottom:4px;">' . htmlspecialchars($bkRow->BookName) . '</div>
                <div style="font-size:12px;color:#6b6b80;">ISBN: ' . htmlspecialchars($bkRow->ISBNNumber ?? 'N/A') . '</div>
              </div>
              <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:20px;">
                <tr>
                  <td style="padding:8px 0;color:#6b6b80;border-bottom:1px solid #f0f0f8;">Borrow Date</td>
                  <td style="padding:8px 0;font-weight:600;color:#1a1a2e;text-align:right;border-bottom:1px solid #f0f0f8;">' . date('d M Y') . '</td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#6b6b80;">Return By</td>
                  <td style="padding:8px 0;font-weight:600;color:#e65100;text-align:right;">' . date('d M Y', strtotime('+7 days')) . '</td>
                </tr>
              </table>
              <p style="font-size:12px;color:#6b6b80;margin:0;">Late returns may incur a fine. Log in to your library account to view your borrowed books anytime.</p>
            </div>
          </div>';
          $mail->send();
        }
      } catch(Exception $e) {
        // Email failure is silent — issue is already recorded
      }
      // ─────────────────────────────────────────────────────────

      $_SESSION['msg']   = "Book issued successfully";
      header('location:manage-issued-books.php');
    } else {
      $_SESSION['error'] = "Something went wrong. Please try again";
      header('location:manage-issued-books.php');
    }
  } else {
    $_SESSION['error'] = "Book not available";
    header('location:manage-issued-books.php');
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Library Admin | Issue New Book</title>
  <link href="assets/css/style.css" rel="stylesheet"/>
  <style>
    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 20px;
    }
    @media(max-width:600px){ .field-row { grid-template-columns: 1fr; } }

    .form-group { margin-bottom: 0; }
    .form-group label {
      display: block;
      font-size: 13px; font-weight: 600;
      margin-bottom: 6px; color: var(--text);
    }
    .form-group label .req { color: #e53935; }
    .form-group input,
    .form-group textarea { width: 100%; box-sizing: border-box; }
    .form-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

    /* Locked field — blue tint */
    input.locked {
      background: #eef1ff !important;
      border-color: #7986cb !important;
      color: var(--text) !important;
      cursor: pointer !important;
    }

    /* Status line under each field */
    .field-status {
      min-height: 20px; margin-top: 5px;
      font-size: 12px; font-weight: 600;
    }
    .field-status.ok    { color: #2e7d32; }
    .field-status.error { color: #c62828; }
    .field-status.muted { color: #888; }

    /* Shared dropdown styles */
    .drop-wrap { position: relative; }
    .dropdown-list {
      display: none;
      position: absolute;
      top: calc(100% + 4px); left: 0; right: 0;
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 10px;
      box-shadow: var(--shadow-md);
      max-height: 280px;
      overflow-y: auto;
      z-index: 200;
    }
    .dropdown-list.open { display: block; }
    .dd-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 14px; cursor: pointer;
      border-bottom: 1px solid #f0ece5;
      transition: background 0.12s;
    }
    .dd-item:last-child { border-bottom: none; }
    .dd-item:hover { background: #f5f0e8; }
    .dd-msg { padding: 13px; text-align: center; font-size: 13px; color: var(--text-muted); }

    /* Student item */
    .dd-item .s-avatar {
      width: 30px; height: 30px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .s-avatar.ok  { background: var(--sage); }
    .s-avatar.bad { background: #e53935; }
    .dd-item .s-info { flex: 1; min-width: 0; }
    .dd-item .s-name {
      font-size: 13px; font-weight: 600; color: var(--text);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dd-item .s-meta { font-size: 11px; color: var(--text-muted); }
    .s-badge {
      font-size: 10px; font-weight: 700;
      padding: 2px 7px; border-radius: 10px; flex-shrink: 0;
    }
    .s-badge.ok  { background: #e8f5e9; color: #2e7d32; }
    .s-badge.bad { background: #ffebee; color: #c62828; }

    /* Book item */
    .dd-item .b-img {
      width: 28px; height: 38px; object-fit: cover;
      border-radius: 3px; border: 1px solid var(--border); flex-shrink: 0;
    }
    .dd-item .b-ph {
      width: 28px; height: 38px; background: #e8e4dc;
      border-radius: 3px; display: flex; align-items: center;
      justify-content: center; flex-shrink: 0; font-size: 14px; color: #aaa;
    }
    .dd-item .b-info { flex: 1; min-width: 0; }
    .dd-item .b-title {
      font-size: 13px; font-weight: 600; color: var(--text);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dd-item .b-meta { font-size: 11px; color: var(--text-muted); }
    .b-avail {
      font-size: 10px; font-weight: 700;
      padding: 2px 7px; border-radius: 10px; flex-shrink: 0;
    }
    .b-avail.ok  { background: #e8f5e9; color: #2e7d32; }
    .b-avail.out { background: #fff3e0; color: #e65100; }

    .section-label {
      font-size: 11px; font-weight: 700; letter-spacing: 1px;
      text-transform: uppercase; color: var(--text-muted);
      margin: 24px 0 14px; padding-bottom: 6px;
      border-bottom: 1px solid var(--border);
    }
    .remark-group { margin-bottom: 20px; }
  </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Issue a New Book</h1>
      <p class="page-subtitle">Fill in student and book details to issue</p>
    </div>
    <a href="manage-issued-books.php" class="btn btn-primary">← Back to Issued Books</a>
  </div>

  <div class="card" style="max-width:720px;margin:0 auto;">
    <div class="card-header"><span class="card-title">Issue Details</span></div>
    <div class="card-body">
      <form method="post" id="issueForm">

        <!-- STUDENT -->
        <div class="section-label">Student Information</div>
        <div class="field-row">

          <div class="form-group">
            <label>Student ID <span class="req">*</span></label>
            <div class="drop-wrap">
              <input type="text" name="studentid" id="sid"
                     placeholder="e.g. STU001" autocomplete="off" required/>
            </div>
            <div class="field-status muted" id="sid-status"></div>
            <div class="form-hint">Type ID → name auto-fills</div>
          </div>

          <div class="form-group">
            <label>Student Name <span class="req">*</span></label>
            <div class="drop-wrap" id="snameWrap">
              <input type="text" id="sname"
                     placeholder="e.g. Ahmad bin Ali" autocomplete="off"/>
              <div class="dropdown-list" id="snameDD"></div>
            </div>
            <div class="field-status muted" id="sname-status"></div>
            <div class="form-hint">Or type name → pick from list</div>
          </div>

        </div>

        <!-- BOOK -->
        <div class="section-label">Book Information</div>
        <div class="field-row">

          <div class="form-group">
            <label>Book Name <span class="req">*</span></label>
            <div class="drop-wrap" id="bnameWrap">
              <input type="text" id="bname"
                     placeholder="Type book title..." autocomplete="off"/>
              <div class="dropdown-list" id="bnameDD"></div>
            </div>
            <div class="field-status muted" id="bname-status"></div>
            <div class="form-hint">Type title → ISBN auto-fills</div>
          </div>

          <div class="form-group">
            <label>ISBN Number <span class="req">*</span></label>
            <div class="drop-wrap">
              <input type="text" id="isbn"
                     placeholder="e.g. 978-3-16-148410-0" autocomplete="off"/>
            </div>
            <div class="field-status muted" id="isbn-status"></div>
            <div class="form-hint">Or type ISBN → name auto-fills</div>
          </div>

        </div>

        <input type="hidden" name="bookid" id="bookid"/>
        <input type="hidden" name="aqty"   id="aqty"/>

        <!-- REMARK -->
        <div class="remark-group">
          <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">
            Remark <span style="color:#e53935;">*</span>
          </label>
          <textarea name="aremark" id="aremark" required
                    placeholder="e.g. Issued at counter"></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" name="issue" id="submitBtn"
                  class="btn btn-gold" disabled>Issue Book</button>
          <a href="manage-issued-books.php" class="btn btn-primary">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/jquery-1.10.2.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════
//  GLOBALS
// ═══════════════════════════════════════════════════════════════
var studentOk = false;
var bookOk    = false;
var sTimer = null, bTimer = null, iTimer = null;
var filling = false;  // guard: prevents oninput firing during JS auto-fill

function checkSubmit(){
  $('#submitBtn').prop('disabled', !(studentOk && bookOk));
}
function status(id, cls, msg){
  $('#'+id).attr('class','field-status '+cls).html(msg);
}
function lock(id, val){
  $('#'+id).val(val).addClass('locked').prop('readonly', true);
}
function unlock(id){
  $('#'+id).val('').removeClass('locked').prop('readonly', false);
}
function esc(s){
  return $('<div>').text(s||'').html();
}
function closeDD(ddId){
  $('#'+ddId).removeClass('open').empty();
}

// ═══════════════════════════════════════════════════════════════
//  STUDENT ID — blur to lookup, fills + locks name
// ═══════════════════════════════════════════════════════════════
$('#sid').on('input', function(){
  if(filling) return;
  filling = true; unlock('sname'); filling = false;
  studentOk = false; checkSubmit();
  status('sid-status','muted',''); status('sname-status','muted','');
});

$('#sid').on('blur', function(){
  var val = $.trim($(this).val());
  if(!val) return;
  status('sid-status','muted','Looking up...');
  $.post('get_student_ajax.php', {studentid: val}, function(r){
    if(r.found && r.active && !r.atLimit){
      filling = true; lock('sname', r.name); filling = false;
      studentOk = true;
      status('sid-status',  'ok', '&#10003; Valid &nbsp;&middot;&nbsp; '+r.activeBorrows+'/3 books borrowed');
      status('sname-status','ok', '&#10003; '+esc(r.email));
    } else if(r.found && r.active && r.atLimit){
      filling = true; lock('sname', r.name); filling = false;
      studentOk = false;
      status('sid-status',  'error','&#10007; Borrow limit reached (3/3)');
      status('sname-status','error','This student must return a book before borrowing another.');
    } else if(r.found){
      filling = true; lock('sname', r.name); filling = false;
      studentOk = false;
      status('sid-status',  'error','&#10007; Account blocked');
      status('sname-status','error','Cannot issue to a blocked account');
    } else {
      studentOk = false;
      status('sid-status',  'error','&#10007; Student ID not found');
      status('sname-status','muted','');
    }
    checkSubmit();
  },'json').fail(function(){ status('sid-status','error','Lookup failed.'); });
});

// ═══════════════════════════════════════════════════════════════
//  STUDENT NAME — live dropdown, pick fills + locks ID
// ═══════════════════════════════════════════════════════════════
$('#sname').on('input', function(){
  if(filling) return;
  filling = true; unlock('sid'); filling = false;
  studentOk = false; checkSubmit();
  status('sid-status','muted',''); status('sname-status','muted','');

  var q = $.trim($(this).val());
  clearTimeout(sTimer);
  if(q.length < 2){ closeDD('snameDD'); return; }

  $('#snameDD').html('<div class="dd-msg">Searching...</div>').addClass('open');
  sTimer = setTimeout(function(){
    $.post('get_student_ajax.php', {studentname: q}, function(list){
      if(!list||!list.length){ $('#snameDD').html('<div class="dd-msg">No students found.</div>'); return; }
      var h = '';
      $.each(list, function(i,s){
        var c = s.active?'ok':'bad', b = s.active?'Active':'Blocked';
        h += '<div class="dd-item" data-sid="'+esc(s.studentid)+'" data-name="'+esc(s.name)+'" data-email="'+esc(s.email)+'" data-active="'+(s.active?1:0)+'">' +
             '<div class="s-avatar '+c+'">'+esc(s.name.charAt(0).toUpperCase())+'</div>' +
             '<div class="s-info"><div class="s-name">'+esc(s.name)+'</div>' +
             '<div class="s-meta">'+esc(s.studentid)+' &nbsp;·&nbsp; '+esc(s.email)+'</div></div>' +
             '<span class="s-badge '+c+'">'+b+'</span></div>';
      });
      $('#snameDD').html(h);
    },'json');
  }, 300);
});

$(document).on('mousedown','#snameDD .dd-item', function(){
  var sid=$(this).data('sid'), name=$(this).data('name'),
      email=$(this).data('email'), active=$(this).data('active')==1;
  closeDD('snameDD');
  filling = true; lock('sname',name); lock('sid',sid); filling = false;

  if(active){
    // Check borrow limit via AJAX after picking from name list
    $.post('get_student_ajax.php', {studentid: sid}, function(r){
      if(r.atLimit){
        studentOk = false;
        status('sname-status','error','&#10007; Borrow limit reached (3/3) — must return a book first');
        status('sid-status',  'error','&#10007; '+r.activeBorrows+'/3 books borrowed');
      } else {
        studentOk = true;
        status('sname-status','ok','&#10003; Student selected &nbsp;&middot;&nbsp; '+r.activeBorrows+'/3 books borrowed');
        status('sid-status',  'ok','&#10003; '+esc(email));
      }
      checkSubmit();
    },'json');
  } else {
    studentOk = false;
    status('sname-status','error','&#10007; Account blocked');
    status('sid-status',  'error','Cannot issue to blocked account');
    checkSubmit();
  }
});

$('#sname').on('blur', function(){ setTimeout(function(){ closeDD('snameDD'); },200); });

$(document).on('click','#sname.locked', function(){
  filling = true; unlock('sname'); filling = false;
  filling = true; unlock('sid');   filling = false;
  studentOk = false; checkSubmit();
  status('sid-status','muted',''); status('sname-status','muted','');
  $(this).focus();
});

// ═══════════════════════════════════════════════════════════════
//  BOOK NAME — live dropdown, pick fills + locks ISBN
// ═══════════════════════════════════════════════════════════════
$('#bname').on('input', function(){
  if(filling) return;
  filling = true; unlock('isbn'); filling = false;
  $('#isbn').val(''); $('#bookid').val(''); $('#aqty').val('');
  bookOk = false; checkSubmit();
  status('bname-status','muted',''); status('isbn-status','muted','');

  var q = $.trim($(this).val());
  clearTimeout(bTimer);
  if(q.length < 2){ closeDD('bnameDD'); return; }

  $('#bnameDD').html('<div class="dd-msg">Searching...</div>').addClass('open');
  bTimer = setTimeout(function(){
    $.post('get_book_ajax.php', {q: q}, function(list){
      if(!list||!list.length){ $('#bnameDD').html('<div class="dd-msg">No books found.</div>'); return; }
      var h = '';
      $.each(list, function(i,b){
        var ac=b.avail>0?'ok':'out', at=b.avail>0?b.avail+' left':'Out of stock';
        var img=b.image?'<img class="b-img" src="bookimg/'+esc(b.image)+'" onerror="this.style.display=\'none\'">':'<div class="b-ph">&#128218;</div>';
        h += '<div class="dd-item" data-id="'+b.id+'" data-title="'+esc(b.title)+'" data-isbn="'+esc(b.isbn)+'" data-avail="'+b.avail+'">' +
             img +
             '<div class="b-info"><div class="b-title">'+esc(b.title)+'</div>' +
             '<div class="b-meta">ISBN: '+esc(b.isbn)+' · '+esc(b.author)+'</div></div>' +
             '<span class="b-avail '+ac+'">'+at+'</span></div>';
      });
      $('#bnameDD').html(h);
    },'json');
  }, 300);
});

$(document).on('mousedown','#bnameDD .dd-item', function(){
  var id=$(this).data('id'), title=$(this).data('title'),
      isbn=$(this).data('isbn'), avail=parseInt($(this).data('avail'));
  closeDD('bnameDD');
  filling = true; lock('bname',title); lock('isbn',isbn); filling = false;
  $('#bookid').val(id); $('#aqty').val(avail);
  if(avail > 0){
    bookOk = true;
    status('bname-status','ok','&#10003; Book selected');
    status('isbn-status', 'ok','&#10003; ISBN locked &nbsp;·&nbsp; '+avail+' copy(s) available');
  } else {
    bookOk = false;
    status('bname-status','error','&#10007; Out of stock');
    status('isbn-status', 'error','No copies available');
  }
  checkSubmit();
});

$('#bname').on('blur', function(){ setTimeout(function(){ closeDD('bnameDD'); },200); });

$(document).on('click','#bname.locked', function(){
  filling = true; unlock('bname'); filling = false;
  filling = true; unlock('isbn');  filling = false;
  $('#bookid').val(''); $('#aqty').val('');
  bookOk = false; checkSubmit();
  status('bname-status','muted',''); status('isbn-status','muted','');
  $(this).focus();
});

// ═══════════════════════════════════════════════════════════════
//  ISBN — type ISBN → book name auto-fills
// ═══════════════════════════════════════════════════════════════
$('#isbn').on('input', function(){
  if(filling) return;
  filling = true; unlock('bname'); $('#bname').val(''); filling = false;
  $('#bookid').val(''); $('#aqty').val('');
  bookOk = false; checkSubmit();
  status('bname-status','muted',''); status('isbn-status','muted','');

  var val = $.trim($(this).val());
  clearTimeout(iTimer);
  if(val.length < 3) return;
  status('isbn-status','muted','Searching...');
  iTimer = setTimeout(function(){
    $.post('get_book_ajax.php', {isbn: val}, function(list){
      if(list && list.length){
        var b = list[0];
        filling = true; lock('bname', b.title); filling = false;
        $('#bookid').val(b.id); $('#aqty').val(b.avail);
        if(b.avail > 0){
          bookOk = true;
          status('isbn-status', 'ok','&#10003; ISBN found');
          status('bname-status','ok','&#10003; '+esc(b.title)+' &nbsp;·&nbsp; '+b.avail+' copy(s)');
        } else {
          bookOk = false;
          status('isbn-status', 'error','&#10007; Out of stock');
          status('bname-status','error',esc(b.title)+' — no copies available');
        }
      } else {
        bookOk = false;
        status('isbn-status','error','&#10007; ISBN not found');
      }
      checkSubmit();
    },'json');
  }, 350);
});

$(document).on('click','#isbn.locked', function(){
  filling = true; unlock('isbn');  $(this).val('');  filling = false;
  filling = true; unlock('bname'); $('#bname').val(''); filling = false;
  $('#bookid').val(''); $('#aqty').val('');
  bookOk = false; checkSubmit();
  status('bname-status','muted',''); status('isbn-status','muted','');
  $(this).focus();
});

// close dropdowns on outside click
$(document).on('click', function(e){
  if(!$(e.target).closest('#snameWrap').length) closeDD('snameDD');
  if(!$(e.target).closest('#bnameWrap').length) closeDD('bnameDD');
});
</script>
</body>
</html>