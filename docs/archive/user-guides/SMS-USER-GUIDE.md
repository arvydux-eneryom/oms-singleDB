# SMS Service - User Guide

**Version:** 1.0
**Last Updated:** October 2025
**For:** End Users, Managers, and Administrators

---

## Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Sending Single SMS](#sending-single-sms)
4. [Sending Bulk SMS](#sending-bulk-sms)
5. [Sending Question SMS](#sending-question-sms)
6. [Understanding SMS Status](#understanding-sms-status)
7. [Viewing SMS History](#viewing-sms-history)
8. [Character Limits & Best Practices](#character-limits--best-practices)
9. [Troubleshooting](#troubleshooting)
10. [FAQ](#faq)

---

## Overview

The SMS Service allows you to send text messages to customers and users directly from your application. You can send individual messages, bulk messages to multiple recipients, or interactive question-based surveys.

### Key Features

- ✉️ **Single SMS**: Send individual messages to specific recipients
- 📢 **Bulk SMS**: Send the same message to multiple recipients at once
- 📋 **Question SMS**: Send interactive survey questions with multiple-choice options
- 📊 **SMS History**: Track all sent messages and their delivery status
- 💰 **Balance Monitoring**: View your Twilio account balance in real-time
- ✅ **Delivery Tracking**: Monitor message status (Queued → Sent → Delivered)

---

## Getting Started

### Accessing the SMS Service

1. Log in to your application
2. Navigate to **Integrations** → **SMS Manager**
3. You'll see the SMS dashboard with three main sections:
   - **Send SMS** (left panel)
   - **SMS History** (right panel)
   - **User List** (bottom panel)

### Account Balance

Your current Twilio balance is displayed at the top-right of the "Send SMS" panel:

```
Balance: 15.42 USD ↻
```

Click the **↻** (refresh) button to update the balance.

⚠️ **Important**: If you see "Balance unavailable", you can click "Check Console" to verify your balance on Twilio's website.

---

## Sending Single SMS

### Step-by-Step Instructions

1. **Enter Recipient Number**
   - Type the phone number in international format
   - Example: `+37064626008`
   - ✅ Spaces are automatically removed

2. **Type Your Message**
   - Maximum 160 characters
   - Character counter updates as you type (e.g., "150 / 160 characters")

3. **Click "Send SMS"**
   - Message is sent immediately
   - Success message appears at the top
   - Message appears in SMS History

### Example

```
Recipient Number: +37064626008
Message: Hello! Your order #12345 has been shipped.

Characters: 148 / 160 characters
```

### Success Indicators

✅ Green message banner: "SMS sent successfully!"
✅ Message appears in SMS History with status "Queued"
✅ Status updates to "Sent" then "Delivered"

---

## Sending Bulk SMS

Use bulk SMS to send the same message to multiple recipients at once.

### Step-by-Step Instructions

1. **Scroll to "User List" Section**

2. **Type Your Message**
   - Enter message in the text area
   - Character counter: "160 / 160 characters"

3. **Select Recipients**
   - Check the boxes next to user names
   - You can select multiple users
   - Only "Active" users can receive SMS

4. **Click "Send SMS to Selected"**
   - Messages are queued for all selected users
   - Each user receives the message individually
   - Success message appears

### Example Workflow

```
Message: "Special offer! 20% off all products this weekend. Use code: SAVE20"

Selected Users:
☑ Arvydas Kavaliauskas (+37064626008)
☑ Arvydas's friend (+37065670928)
☑ Animesh Chowdhury (+37067160181)

Result: 3 SMS messages sent
```

### User Status Indicators

| Status | Color | Can Receive SMS? |
|--------|-------|------------------|
| Active | 🟢 Green | ✅ Yes |
| Inactive | 🔴 Red | ❌ No |

---

## Sending Question SMS

Question SMS allows you to send interactive surveys with multiple-choice options.

### How It Works

```
┌─────────────────────────────────────┐
│ 1. You send question SMS            │
│    "Which plan do you prefer?"      │
│    1. Basic                          │
│    2. Standard                       │
│    3. Premium                        │
│    4. VIP                            │
└─────────────────────────────────────┘
           ↓
┌─────────────────────────────────────┐
│ 2. User receives SMS & replies "3"  │
└─────────────────────────────────────┘
           ↓
┌─────────────────────────────────────┐
│ 3. System confirms                   │
│    "You selected: Premium"           │
└─────────────────────────────────────┘
```

### Step-by-Step Instructions

1. **Locate "Send Question" Section** (bottom of page)

2. **Enter Recipient Number**
   - Type: `+37064626008`

3. **Click "Send Question"**
   - A random question from the database is sent
   - Question includes multiple-choice options
   - User can reply with a number (1, 2, 3, 4)

4. **Automatic Confirmation**
   - When user replies, system validates the answer
   - Sends confirmation: "You selected: [option]"
   - Stores response in database

### Question Format

Questions are sent with this format:

```
Welcome back!

Which plan do you prefer?
1. Basic
2. Standard
3. Premium
4. VIP
```

**First-time users** see "Welcome to our service!"
**Returning users** see "Welcome back!"

### Answer Validation

✅ **Valid**: User replies "1", "2", "3", or "4"
❌ **Invalid**: User replies "5", "A", or any other text
🔄 **Same Question**: Users can answer the same question on different days

---

## Understanding SMS Status

Every SMS goes through several statuses before delivery:

### Status Flow

```
Queued → Sent → Delivered
```

| Status | Icon | Description |
|--------|------|-------------|
| **Queued** | 🟡 Gray | Message accepted by Twilio, waiting to send |
| **Sent** | 🟡 Yellow | Message sent to carrier, en route to recipient |
| **Delivered** | 🟢 Green | Message successfully delivered to recipient |
| **Undelivered** | 🔴 Red | Message failed to deliver (invalid number, blocked, etc.) |

### Timeline

- **Queued → Sent**: Usually instant (< 1 second)
- **Sent → Delivered**: 2-10 seconds (depends on carrier)

### Troubleshooting Delivery Issues

If message shows **"Undelivered"**:

1. ✅ Verify phone number format (`+` and country code)
2. ✅ Check if number is valid and active
3. ✅ Ensure sufficient account balance
4. ✅ Verify number is not on a block list

---

## Viewing SMS History

SMS History displays your 10 most recent messages.

### Information Displayed

| Column | Example | Description |
|--------|---------|-------------|
| **Recipient** | +37064626008 | Who received the message |
| **Message** | Hello! Your order... | Preview (first 20 chars) |
| **Status** | Delivered | Current delivery status |
| **Date** | 2025-10-15 18:29 | When message was sent |

### Auto-Refresh

SMS History updates automatically every 2 seconds to show:
- New messages you send
- Updated delivery statuses

### Color Coding

- 🟢 **Green Badge**: Delivered
- 🟡 **Yellow Badge**: Sent
- 🟡 **Gray Badge**: Queued
- 🔴 **Red Badge**: Undelivered

---

## Character Limits & Best Practices

### Character Limit: 160 Characters

SMS messages are limited to **160 characters**. The counter updates in real-time as you type.

#### What Counts as Characters?

- Letters: a-z, A-Z
- Numbers: 0-9
- Spaces and punctuation: `.`, `!`, `?`, `,`
- Special characters: `@`, `#`, `$`
- Emojis: **Count as multiple characters** (2-4 each)

### Writing Effective SMS

✅ **DO:**
- Be concise and clear
- Include call-to-action
- Add context (order number, dates)
- Use proper capitalization
- Test messages before bulk sending

❌ **DON'T:**
- Use excessive punctuation!!!
- Write in all caps
- Include long URLs (use URL shorteners)
- Send without proofreading
- Spam users with frequent messages

### Example Messages

**Good Example** (48 characters):
```
Your order #12345 ships today. Track: bit.ly/xyz
```

**Bad Example** (too long):
```
Hello valued customer! We are writing to inform you that your order number 12345 has been processed and will be shipped today via our premium courier service.
```

---

## Troubleshooting

### Common Issues & Solutions

#### ❌ "The to field format is invalid"

**Problem**: Phone number format is incorrect
**Solution**: Use international format with `+` and country code

```
✅ Correct: +37064626008
❌ Wrong: 37064626008
❌ Wrong: 064626008
❌ Wrong: +370 646 26008 (remove spaces - they're auto-removed now)
```

#### ❌ "SMS service temporarily unavailable"

**Problem**: Insufficient Twilio account balance
**Solution**:
1. Check balance indicator (top-right)
2. Add funds to your Twilio account
3. Refresh balance using ↻ button

#### ❌ Messages stuck in "Queued" status

**Problem**: Webhook configuration issue or network delay
**Solution**:
1. Wait 30 seconds (network delays)
2. Refresh the page
3. Check if message was actually delivered (ask recipient)
4. Contact support if persists

#### ❌ "Cannot send SMS to this country"

**Problem**: Geographic permissions not enabled for that country
**Solution**: Enable geographic permissions in Twilio Console

#### ❌ No confirmation after user replies to question

**Problem**: User sent invalid answer or duplicate
**Solution**:
- Ensure user replies with valid option number (1, 2, 3, or 4)
- Check if user already answered this specific question instance
- Users can answer same question on different days

---

## FAQ

### General Questions

**Q: How much does each SMS cost?**
A: Pricing depends on your Twilio plan and destination country. Check your Twilio pricing page.

**Q: Can I send SMS internationally?**
A: Yes, if you have geographic permissions enabled for that country in Twilio.

**Q: How many SMS can I send at once?**
A: Bulk SMS supports sending to multiple users simultaneously. Rate limits apply (10 SMS per minute per user).

**Q: Can I schedule SMS for later?**
A: Not currently. All SMS are sent immediately.

### Bulk SMS Questions

**Q: What happens if one recipient's number is invalid?**
A: Other messages still send successfully. The invalid one shows "Undelivered" in history.

**Q: Can I select all users at once?**
A: Use the checkbox in the table header to select/deselect all.

**Q: How do I know bulk SMS finished sending?**
A: Check SMS History - all messages will appear with their individual statuses.

### Question SMS Questions

**Q: Can I create custom questions?**
A: Questions are managed in the database. Contact your developer/admin to add new questions.

**Q: What if user replies with wrong format?**
A: System ignores invalid answers. No confirmation is sent. User can reply again.

**Q: Can users answer the same question twice?**
A: Yes, on different days. They cannot answer the same question instance twice.

**Q: What happens to the responses?**
A: All responses are stored in the database for analysis and reporting.

### Account & Balance

**Q: Why does my balance show "unavailable"?**
A: This can happen due to API issues. Click "Check Console" to view your balance on Twilio's website.

**Q: How often should I check my balance?**
A: Balance is cached for 5 minutes. Use ↻ to force refresh.

**Q: Will I be notified when balance is low?**
A: Not automatically. Monitor your balance regularly to avoid service interruption.

---

## Support & Contact

For technical issues or questions not covered in this guide:

1. **Check Error Messages**: Most errors include helpful hints
2. **Review FAQ Section**: Common questions answered above
3. **Contact Support**: Reach out to your system administrator
4. **Twilio Console**: https://console.twilio.com for account issues

---

## Appendix: Phone Number Formats

### International Format Requirements

All phone numbers must use **E.164 format**:

```
+[country code][area code][phone number]
```

### Examples by Country

| Country | Example | Format |
|---------|---------|--------|
| Lithuania | +37064626008 | +370 XXXXXXXX |
| United States | +14155551234 | +1 XXXXXXXXXX |
| United Kingdom | +447911123456 | +44 XXXXXXXXXXX |
| Germany | +491234567890 | +49 XXXXXXXXXXX |
| India | +919876543210 | +91 XXXXXXXXXX |

### Common Mistakes

❌ Missing `+`: `37064626008`
❌ Missing country code: `064626008`
❌ With spaces: `+370 646 26008` (auto-removed now)
❌ With dashes: `+370-646-26008`
❌ With parentheses: `+370 (646) 26008`

---

**End of User Guide**
