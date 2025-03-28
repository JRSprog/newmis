<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestlink College of the Philippines</title>
    <link rel="shortcut icon" href="./uploads/blogo.png" type="x-icon">
    <style>
:root {
  --primary: #191970;
  --secondary: #5218fa;
  --accent: #00d4ff;
  --light: #f8f9fa;
  --dark: #212529;
  --success: #28a745;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', Arial, sans-serif;
}

body {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0;
  justify-content: space-between;
  overflow-x: hidden;
  background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
              url('uploads/mvb.jpg') no-repeat center center fixed;
  background-size: cover;
  color: var(--light);
  line-height: 1.6;
}

/* Header Styles */
header {
  background: rgba(25, 25, 112, 0.9);
  width: 100%;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  backdrop-filter: blur(10px);
  box-shadow: var(--shadow);
  position: relative;
  z-index: 100;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo {
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(to right, var(--accent), var(--secondary));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  transition: var(--transition);
}

.logo:hover {
  transform: scale(1.05);
}

.img1 {
  width: 70px;
  height: 70px;
  object-fit: contain;
  filter: drop-shadow(0 0 5px rgba(0, 212, 255, 0.5));
  transition: var(--transition);
}

.img1:hover {
  transform: rotate(15deg);
}

/* Navigation Menu */
.menu {
  display: flex;
  gap: 1.5rem;
  list-style: none;
  padding: 0;
}

.menu a {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  transition: var(--transition);
  position: relative;
}

.menu a::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2px;
  background: var(--accent);
  transition: var(--transition);
  transform: translateX(-50%);
}

.menu a:hover {
  color: var(--accent);
}

.menu a:hover::after {
  width: 100%;
}

/* Main Content Container */
.main-content {
  width: 100%;
  max-width: 1200px;
  padding: 2rem;
  margin: 2rem auto;
}

/* Vision & Mission Section */
.bcpvm {
  width: 100%;
  margin: 2rem auto;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  border-radius: 24px;
  padding: 3rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-start;
  gap: 2rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: var(--transition);
}

.bcpvm h1 {
  font-size: 2.5rem;
  width: 100%;
  color: var(--primary);
  margin-bottom: 1.5rem;
  text-align: center;
  position: relative;
}

.bcpvm h1::after {
  content: '';
  display: block;
  width: 100px;
  height: 4px;
  background: linear-gradient(to right, var(--accent), var(--secondary));
  margin: 0.5rem auto 0;
  border-radius: 2px;
}

.vision, .mission {
  background: linear-gradient(135deg, var(--secondary), #3a0ca3);
  width: calc(50% - 1rem);
  padding: 2.5rem;
  color: white;
  border-radius: 16px;
  box-shadow: var(--shadow);
  transition: var(--transition);
  text-align: center;
  position: relative;
  overflow: hidden;
}

.vision::before, .mission::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
  transform: rotate(45deg);
  transition: var(--transition);
}

.vision:hover, .mission:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.vision:hover::before, .mission:hover::before {
  transform: rotate(45deg) translate(10%, 10%);
}

.vision h2, .mission h2 {
  font-size: 1.8rem;
  margin-bottom: 1rem;
  position: relative;
  z-index: 1;
}

.vision p, .mission p {
  font-size: 1.1rem;
  line-height: 1.8;
  position: relative;
  z-index: 1;
}

/* Slide Effect */
.bcpvm.move-left {
  transform: translateX(-130%);
}

/* Section Styles (now numbered) */
.section1, .section2, .section3 {
  width: 90%;
  max-width: 1000px;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  border-radius: 24px;
  padding: 3rem;
  margin-top: 80px;
  text-align: center;
  position: fixed;
  left: -100%;
  top: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.5s ease-in-out;
  opacity: 0;
  z-index: 10;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  overflow-y: auto;
  max-height: 80vh;
}

.section1 h2, .section2 h2, .section3 h2 {
  font-size: 2.2rem;
  color: var(--primary);
  margin-bottom: 2rem;
  position: relative;
}

.section1 h2::after, .section2 h2::after, .section3 h2::after {
  content: '';
  display: block;
  width: 80px;
  height: 4px;
  background: linear-gradient(to right, var(--accent), var(--secondary));
  margin: 0.5rem auto 0;
  border-radius: 2px;
}

.section1 p, .section2 p, .section3 p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
  color: var(--dark);
}

.section1 ul, .section2 ul, .section3 ul {
  list-style: none;
  padding-left: 0;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 1rem;
}

.section1 ul li, .section2 ul li, .section3 ul li {
  font-size: 1.1rem;
  padding: 1rem;
  width: calc(50% - 0.5rem);
  text-align: left;
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(82, 24, 250, 0.1);
  border-radius: 8px;
  transition: var(--transition);
}

.section1 ul li:hover, .section2 ul li:hover, .section3 ul li:hover {
  background: rgba(82, 24, 250, 0.2);
  transform: translateX(5px);
}

.section1 ul li::before, .section2 ul li::before, .section3 ul li::before {
  content: "✓";
  color: var(--success);
  font-weight: bold;
  font-size: 1.3rem;
  min-width: 20px;
}

.section1.show, .section2.show, .section3.show {
  left: 50%;
  opacity: 1;
}

/* Campus & Contact Section */
.cus, .campus {
  background: linear-gradient(135deg, var(--secondary), #3a0ca3);
  padding: 2.5rem;
  margin: 2rem 0;
  border-radius: 16px;
  box-shadow: var(--shadow);
  text-align: center;
  color: white;
  transition: var(--transition);
}

.cus:hover, .campus:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.cus h2, .campus h2 {
  font-size: 2rem;
  margin-bottom: 1.5rem;
}

.cus p, .campus p {
  font-size: 1.1rem;
  line-height: 1.8;
  margin-bottom: 1.5rem;
}

.campus img {
  width: 100%;
  max-width: 800px;
  border-radius: 12px;
  margin-top: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  transition: var(--transition);
  border: 3px solid white;
}

.campus img:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

/* Footer */
footer {
  background: linear-gradient(135deg, var(--primary), #000033);
  color: white;
  text-align: center;
  padding: 3rem 2rem;
  width: 100%;
  margin-top: 3rem;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 2rem;
}

.footer-section {
  flex: 1;
  min-width: 250px;
  text-align: left;
}

.footer-section h3 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  display: inline-block;
}

.footer-section h3::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 50px;
  height: 3px;
  background: var(--accent);
}

.footer-section p, .footer-section a {
  margin-bottom: 1rem;
  display: block;
  color: rgba(255, 255, 255, 0.8);
  transition: var(--transition);
  text-decoration: none;
}

.footer-section a:hover {
  color: var(--accent);
  transform: translateX(5px);
}

.social-icons {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.social-icons a {
  color: white;
  font-size: 1.5rem;
  transition: var(--transition);
}

.social-icons a:hover {
  color: var(--accent);
  transform: translateY(-5px);
}

.copyright {
  width: 100%;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-in {
  animation: fadeIn 0.8s ease-out forwards;
}

/* Responsive Design */
@media (max-width: 992px) {
  .vision, .mission {
    width: 100%;
  }
  
  .section1 ul li, .section2 ul li, .section3 ul li {
    width: 100%;
  }
}

@media (max-width: 768px) {
  header {
    flex-direction: column;
    padding: 1rem;
    gap: 1rem;
  }
  
  .logo-container {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .logo {
    font-size: 2rem;
    margin-bottom: 0;
  }
  
  .img1 {
    width: 50px;
    height: 50px;
  }
  
  .menu {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .menu a {
    font-size: 0.9rem;
    padding: 0.3rem 0.8rem;
  }
  
  .bcpvm {
    padding: 1.5rem;
  }
  
  .bcpvm h1 {
    font-size: 1.8rem;
  }
  
  .vision, .mission {
    padding: 1.5rem;
  }
  
  .section1, .section2, .section3 {
    width: 95%;
    padding: 1.5rem;
    max-height: 75vh;
    margin-top: 200px;
  }
}

@media (max-width: 480px) {
  .logo {
    font-size: 1.5rem;
  }
  
  .section1 h2, .section2 h2, .section3 h2 {
    font-size: 1.5rem;
  }
  
  .section1 ul li, .section2 ul li, .section3 ul li {
    font-size: 1rem;
  }
  
  .cus, .campus {
    padding: 1.5rem;
  }
}
    </style>
</head>
<body>
    <header>
        <img src="./uploads/blogo.png" class="img1">
        <div class="logo">Bestlink College of the Philippines</div>
        <ul class="menu">
            <li><a id="about-link">About</a></li>
            <li><a id="programs-link">Programs</a></li>
            <li><a id="contact-link">Contact/Campuses</a></li>
            <li><a href="login.php">Sign In</a></li>
        </ul>
    </header>   

    <div class="bcpvm" id="bcpvm">
        <h1>BCP VISION AND MISSION</h1>
        <div class="vision">
            <h2>VISION</h2><br>
            <p>Bestlink College of the Philippines is committed to provide and promote equality education which unique 
                and modern and research-based curriculum with delivery system geared towards excellence.</p>
        </div>
        <div class="mission">
            <h2>MISSION</h2><br>
            <p>To produce a self-motivated and self-directed individual who aims for academic excellence, 
                god-fearing, peaceful, healthy, productive and successful citizen.</p>
        </div>
    </div>

    <!-- About Section -->
    <div class="section1" id="about">
        <img src="./uploads/blogo.png" alt="BCP Logo" style="width: 10%;">
        <h2>About BCP</h2><br><br>
        <p style="font-size: 20px;">At Bestlink College of the Philippines, We provide and promote quality education with modern and 
        unique techniques to able to enhance the skill and the knowledge of our dear students to make them globally competitive and productive citizens.</p>
    </div>

    <!-- Contact Section -->
    <div class="section2" id="contact">
        <h2>Contact and Campuses</h2><br><br>
        <div class="cus">
            <h3>Contact Us</h3><br>
            <p>#1071 Brgy. Kaligayahan, Quirino Highway
            Novaliches Quezon City, Philippines 1123</p><br>

            <p>Contact #: 417-4355 <br><br>
            Email: bcp-inquiry@bcp.edu.ph</p>
        </div>
        <div class="campus">
            <h3>BCP Campuses</h3><br>
            <img src="./uploads/all.jpg" alt="Millionaire's Village Campus"><br><br>
            <p>Millionaire's Village Campus</p><br>
            <p>Main Campus</p><br>
            <p>Bulacan Campus</p><br>
        </div>
    </div>

    <!-- Programs Section -->
    <div class="section3" id="programs">
        <h2>Our Programs</h2><br>
        <ul>
            <li>Bachelor of Science in Information Technology</li>
            <li>Bachelor of Science in Business Administration</li>
            <li>Bachelor of Science in Psychology</li>
            <li>Bachelor of Science in Criminology</li>
            <li>Bachelor of Science in Elementary Education</li>
            <li>Bachelor of Science in Secondary Education</li>
            <li>Bachelor of Science in Tourism Management</li>
            <li>Bachelor of Science in Physical Education</li>
            <li>Bachelor of Science in Computer Engineering</li>
            <li>Bachelor of Science in Entrepreneurship</li>
            <li>Bachelor of Science in Accounting Information System</li>
            <li>Bachelor of Science in Technological and Livelihood Education</li>
            <li>Bachelor of Science in Information Science</li>
            <li>Bachelor of Science in Office Administration</li>
        </ul>
    </div>
    <footer>All Rights Reserved 2025. BCP</footer>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const bcpvm = document.querySelector("#bcpvm");
            const aboutSection = document.querySelector("#about");
            const programsSection = document.querySelector("#programs");
            const contactSection = document.querySelector("#contact");
            const aboutLink = document.querySelector("#about-link");
            const contactLink = document.querySelector("#contact-link");
            const programsLink = document.querySelector("#programs-link");

            function closeAll() {
                aboutSection.classList.remove("show");
                programsSection.classList.remove("show");
                contactSection.classList.remove("show");
                bcpvm.classList.remove("move-left");
                document.body.style.overflow = 'auto';
            }

            function openSection(section) {
                closeAll();
                section.classList.add("show");
                bcpvm.classList.add("move-left");
                document.body.style.overflow = 'hidden';
            }

            aboutLink.addEventListener("click", function (e) {
                e.preventDefault();
                if (!aboutSection.classList.contains("show")) {
                    openSection(aboutSection);
                } else {
                    closeAll();
                }
            });

            programsLink.addEventListener("click", function (e) {
                e.preventDefault();
                if (!programsSection.classList.contains("show")) {
                    openSection(programsSection);
                } else {
                    closeAll();
                }
            });

            contactLink.addEventListener("click", function (e) {
                e.preventDefault();
                if (!contactSection.classList.contains("show")) {
                    openSection(contactSection);
                } else {
                    closeAll();
                }
            });

            // Close modal when clicking outside content
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('section1') || 
                    e.target.classList.contains('section2') || 
                    e.target.classList.contains('section3')) {
                    closeAll();
                }
            });
        });
    </script>
</body>
</html>