<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
    <link rel="stylesheet" href="./CSS/index.css">
    <link rel="stylesheet" href="./CSS/faq.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg">
</head>
<body>
    <?php include ("./header.php"); ?>
    
    <div class="topic">
    <img src="./Images/FAQ-1.jpg" alt="FAQ Image" class="FAQImage" >
	<div class ="topic1">
	<h1>Got A Question?</h1> 
	<h3>Find our FAQ here.If your question hasn't been answerd here please <a href="contact.php">contact us </a> </h3>
    </div>	</div>



    <div class="FAQ">
        <h2>Frequently Asked Questions</h2>

        
            <div class="faq-question">1.What services does this wesite provide? </div>
            <div class="faq-answer">Our website offers prescription refills, over-the-counter medicines, health consultations, and home delivery services.</div>
        
            <div class="faq-question">2.How do I order a product?</div>
            <div class="faq-answer">First you should logging to the our website and create your own accout.Then you can vist our order page in our Remedy Pharmacy website.The you can make your own orders</div>
        
            <div class="faq-question">3.How long does it take to process a prescription?</div>
            <div class="faq-answer">Most prescriptions are processed within 1-2 business days. For same-day pickup, please place your request by [cut-off time].</div>
        
            <div class="faq-question">4.Can I pay for my prescription online?</div>
            <div class="faq-answer">No.You can't pay online.You can do your payment to the bank and opload your bank recipte to the website.</div>
        
            <div class="faq-question">5.Are your products genuine and safe?</div>
            <div class="faq-answer">All our products are sourced from licensed suppliers and meet strict quality and safety standards.</div>
        
            <div class="faq-question">6.Is my personal information secure on your website?</div>
            <div class="faq-answer">Yes, we use encryption and other security measures to ensure your personal and payment information is protected.</div>
        
            <div class="faq-question">7.Do you offer medication counseling?</div>
            <div class="faq-answer">Yes, our pharmacists can provide detailed information on how to take your medication, potential side effects, and drug interactions.</div>
        
            <div class="faq-question">8.Can I view my prescription history online?</div>
            <div class="faq-answer">Yes, by logging into your account, you can view your prescription history, refill requests, and order status.</div>
        
            <div class="faq-question">9.Can I cancel my order?</div>
            <div class="faq-answer">You can cancel an order before it has been processed by contacting our support team or through your account dashboard.</div>
        
            <div class="faq-question">10.Can I return a medication?</div>
            <div class="faq-answer">Due to health and safety regulations, we cannot accept returns on prescription medications. However, OTC products may be eligible for return under certain conditions.</div>
        
    </div>

    <script src="./js/faq.js"></script>
    


    <?php include ("./footer.php"); ?>
</body>
</html>