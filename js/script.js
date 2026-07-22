lucide.createIcons();

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  fetchClasses();
});

// ۱. مدیریت دارک مود / لایت مود
function initTheme() {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    document.body.classList.add('light-theme');
    updateThemeIcon('moon');
  } else {
    updateThemeIcon('sun');
  }
}

function toggleTheme() {
  document.body.classList.toggle('light-theme');
  const isLight = document.body.classList.contains('light-theme');
  
  localStorage.setItem('theme', isLight ? 'light' : 'dark');
  updateThemeIcon(isLight ? 'moon' : 'sun');
}

function updateThemeIcon(iconName) {
  const iconElement = document.getElementById('themeIcon');
  if (iconElement) {
    iconElement.setAttribute('data-lucide', iconName);
    lucide.createIcons();
  }
}

// ۲. دریافت لیست کلاس‌ها
async function fetchClasses() {
  try {
    const response = await fetch('api.php?action=get_classes');
    const classes = await response.json();
    
    const classSelect = document.getElementById("classSelect");
    classSelect.innerHTML = "";

    classes.forEach(cls => {
      const option = document.createElement("option");
      option.value = cls.id;
      option.text = cls.name;
      classSelect.add(option);
    });

    onClassChange();
  } catch (error) {
    console.error('خطا در دریافت لیست کلاس‌ها:', error);
  }
}

// ۳. تغییر کلاس
async function onClassChange() {
  const classId = document.getElementById("classSelect").value;
  if (!classId) return;

  fetchSubjects(classId);
  fetchStudents(classId);
}

// دریافت لیست درس‌ها
async function fetchSubjects(classId) {
  try {
    const response = await fetch(`api.php?action=get_subjects&class_id=${classId}`);
    const subjects = await response.json();
    
    const subjectSelect = document.getElementById("subjectSelect");
    subjectSelect.innerHTML = "";

    subjects.forEach(sub => {
      const option = document.createElement("option");
      option.value = sub.id;
      option.text = sub.title;
      subjectSelect.add(option);
    });
  } catch (error) {
    console.error('خطا در دریافت درس‌ها:', error);
  }
}

// دریافت لیست دانش‌آموزان
async function fetchStudents(classId) {
  try {
    const response = await fetch(`api.php?action=get_students&class_id=${classId}`);
    const students = await response.json();
    
    const tbody = document.getElementById("studentTableBody");
    tbody.innerHTML = "";

    document.getElementById("studentCount").innerText = `تعداد: ${students.length} نفر`;

    students.forEach((student, index) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${index + 1}</td>
        <td>${student.national_id || '---'}</td>
        <td>${student.name} ${student.family}</td>
        <td><input type="number" class="score-input" data-student-id="${student.id}" min="0" max="20" step="0.25" placeholder="مثال: 18.5"></td>
      `;
      tbody.appendChild(tr);
    });
  } catch (error) {
    console.error('خطا در دریافت دانش‌آموزان:', error);
  }
}

// ۴. کنترل ورود نمره (۰ تا ۲۰)
document.addEventListener('input', function (e) {
  if (e.target.classList.contains('score-input')) {
    let val = parseFloat(e.target.value);
    if (val > 20) e.target.value = 20;
    if (val < 0) e.target.value = 0;
  }
});

// ۵. مدال و ارسال اطلاعات
function openModal() {
  const classSelect = document.getElementById("classSelect");
  const className = classSelect.options[classSelect.selectedIndex]?.text || '';
  
  const subjectSelect = document.getElementById("subjectSelect");
  const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text || '';

  document.getElementById("modalMessage").innerText = 
    `آیا از ثبت نهایی نمرات درس «${subjectName}» برای کلاس «${className}» اطمینان دارید؟`;
    
  document.getElementById("confirmModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("confirmModal").style.display = "none";
}

async function processSubmit() {
  closeModal();

  const submitBtn = document.getElementById("submitBtn");
  const btnText = submitBtn.querySelector("span");
  
  submitBtn.disabled = true;
  btnText.innerText = "در حال ارسال اطلاعات...";

  const scoresData = [];
  const rows = document.querySelectorAll("#studentTableBody tr");

  rows.forEach(row => {
    const input = row.querySelector('.score-input');
    scoresData.push({
      student_id: input.dataset.studentId,
      score: input.value !== '' ? input.value : null
    });
  });

  const payload = {
    action: "save_scores",
    class_id: document.getElementById("classSelect").value,
    subject_id: document.getElementById("subjectSelect").value,
    period: document.getElementById("periodSelect").value,
    scores: scoresData
  };

  try {
    const response = await fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ نمرات با موفقیت ثبت شدند!");
    } else {
      alert("❌ خطا در ثبت نمرات: " + (result.message || 'خطایی رخ داد'));
    }
  } catch (error) {
    alert("❌ خطا در ارتباط با سرور");
  } finally {
    submitBtn.disabled = false;
    btnText.innerText = "ثبت نهایی نمرات";
  }
}
