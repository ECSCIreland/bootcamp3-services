# mail

This document outlines vulnerabilities found in the ``mail`` service found as part of the ``navashield`` service.

## Signature Checking Vulnerability

### Program Functionality

The mail service allows the user to start email threads by providing certain information as,

* Sender Address (just to know who sent it)
* Sender Modulus (used in signature generation)
* Message (the contents of the email)
* Attachments (various other attachments)

After creating an email thread, we receive an thread ID. When the attack-defence bootcamp was going on, the flag bot would provide a list of email thread IDs, which would contain flags.

Using these IDs, we can reply to an email thread, this functionality allows us to look at the whole thread and receive the flag!

In order to view the thread, we must give the following information,

* Thread ID (to load the required thread)
* Message (to response to the email thread)
* Signature (to verify the sender)

In this scenario, we control both the **message** and the **signature** which allows us to craft inputs to break through this signature check.

### Reverse Engineering

In order to see how the program verifies the signatures, we reverse engineer and find the function responsible for it.

Here we find the following decompiled function code,

```c
bool signature_check(int64_t signature, int64_t modulus, const char *message)
{
  int64_t signature_bn  = 0;
  char signature_rsa[32];
  char signature_sha[40];

  if (!BN_hex2bn(&signature_bn, signature))
    print_error("cannot import sig");

  // Generation of RSA signature.
  BN_mod_exp(signature_bn, signature_bn, fixed_exp, modulus, bn_ctx);
  BN_mask_bits(signature_bn, 256);
  BN_bn2binpad(signature_bn, signature_rsa, 32);
  BN_free(signature_bn);

  // Generation of SHA256 signature.
  SHA256(message, strlen(message), signature_sha);

  // Signature check.
  return strncmp(signature_rsa, signature_sha, 32) == 0;
}
```

On first analysis of the code, not much shows to be an issue, but we must find a way to pass the signature check at the bottom through generating an RSA signature equal to the SHA256 signature.

This would be infeasible as there is an easier way to get around this and pass the check, through the ``strncmp``.

For checking of cryptographic signatures, or important data, ``strncmp`` is not a good idea as it will stop comparision after encountering a NUL byte!

This in our case is perfect as we just need to get both signatures to have a NUL byte at the start and they will pass the check!

### Getting the Flags

From the decompiled code, we see that 2 signatures are generated, one RSA and one SHA256.

#### SHA256 Signature

For the SHA256 signature, it simply takes in our inputted message and gets the SHA256 hash of the message. In order to get this side to contain a NUL byte, we just need to find a message that gives a hash with a NUL byte in its starting bytes!

Some simple PoC code for this (written in Rust but can be adapted),

```rust
use openssl::sha;
use rand::distributions::Alphanumeric;

fn main() {
    loop {
        // Generate a random alphanumeric string of length 32 and get hash.
        let random = Alphanumeric.sample_string(&mut rand::thread_rng(), 32);
        let sha256_hash = sha::sha256(random.as_bytes());

        // Check if the first 3 bytes are NUL bytes, 
        // this is overkill but just to make sure it passes.
        if sha256_hash[0..3].eq(&[0, 0, 0]) {
            // If found, print the input string and its output bytes array.
            println!("Input {:?} - Output {:?}", random, sha256_hash);
            break;
        }
    }
}
```

Running this eventually returns us a hash which we can input as our **message** when replying to the email thread!

**Example Output**: N6yzbvLF0AwEdrYVTyw0VPFymU43Cqtz - [**0, 0, 0**, ...]

---

#### RSA Signature

For the RSA signature, it simply takes in our inputted signature and decrypts signature using the formula,

$$ m = s^e \ mod \ n$$

From further reverse engineering work, we find that our value for **e** is hardcoded as **65537**.

We are also given the modulus **n** from the prompt as we put in the **message** when replying to the thread.

Therefore, we are able to input random decimal numbers for **s** converted from a hexadecimal string as the decompiled code represents it using BigNumbers's.

Inputting all our variables into the formula, we can convert the value **m** to an array of bytes and check if it contains NUL bytes.

Some simple PoC code for this (written in Rust but can be adapted),

```rust
use openssl::bn::{BigNum, BigNumContext};
use rand::RngCore;

fn main() {
    // Generate random hex string.
    let mut bytes = [0; 8];
    rand::thread_rng().fill_bytes(&mut bytes);

    // Encode the bytes to a string and convert it to a BigNumber.
    let bytes_str = hex::encode(&bytes);
    let sign_hex = BigNum::from_hex_str(&bytes_str).unwrap();

    // Prepare the same format as the decompiled code.
    let mut ctx = BigNumContext::new().unwrap();
    let e = BigNum::from_u32(65537).unwrap();

    // NOTE: This changes depending on the set modulus.
    // For testing purposes, I had it set to 10.
    let modulus = BigNum::from_u32(10).unwrap();

    // Run the formula.
    let mut result = BigNum::new().unwrap();
    result.mod_exp(&sign_hex, &e, &modulus, &mut ctx).unwrap();

    // Convert it back to a bytes array padded to length 32.
    let decrypt_sign = result.to_vec_padded(32).unwrap();

    println!("Input {} - Output {:?}", bytes_str, decrypt_sign);
}
```

Automating this to find a NUL byte in our output is possible and is left as an exercise for the reader :)

In the case for modulus 10, we get an output like this,

**Example Output**: 582db4e04c3d6427 - [0, 0, 0, ...]

This value can be used in our **signature** when replying to the email thread!

---

### Final Cleanup

We now have our values which we can use to trick the ``strncmp`` function and bypass the signature check and get the flag!

## Signature Checking Patching

As this is an attack-defence challenge, it would make sense to patch this to prevent other teams from exploiting it.

In this situation, we can use ``memcmp`` to replace the ``strncmp`` which will fix the comparision vulnerability.

It is still a WIP to patch this successfully, and once it has been patched i'll fill the rest of this part out!

## Extra Information

This whole vulnerability is what happened to the Nintendo Wii! More commonly known as the **Signing Bug**, which you can read up more [here](http://wiibrew.org/wiki/Signing_bug).