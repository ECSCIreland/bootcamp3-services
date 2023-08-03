#define _GNU_SOURCE
#include <dlfcn.h>
#include <stdio.h>

typedef int *(*memcmp_t)(const void *s1, const void *s2, size_t n);
memcmp_t real_memcmp;

typedef int *(*strncmp_t)(const char *s1, const char *s2, size_t n);
strncmp_t real_strncmp;

int *strncmp(const char *s1, const char *s2, unsigned long n) {
    // Some pretty-printing to allow us to know when we are redirecting.
    fprintf(stderr, "redirecting strncmp(%s, %s)\n", s1, s2);

    // Check if we are the real strncmp and if so,
    if (!real_strncmp) {
        // get the address to memcmp.
        real_memcmp = dlsym(RTLD_NEXT, "memcmp");
    }

    // Redirect the arguments to memcmp.
    return real_memcmp(s1, s2, n);
}

__attribute__((constructor)) static void setup(void) {
    fprintf(stderr, "patch loaded\n");
}